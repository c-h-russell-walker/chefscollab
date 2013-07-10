<?php

class UpdraftPlus_BackupModule_s3 {

	function getS3($key, $secret) {
		return new S3($key, $secret);
	}

	function set_endpoint($obj, $region) {
		switch ($region) {
			case 'EU':
			case 'eu-west-1':
				$endpoint = 's3-eu-west-1.amazonaws.com';
				break;
			case 'us-west-1':
			case 'us-west-2':
			case 'ap-southeast-1':
			case 'ap-southeast-2':
			case 'ap-northeast-1':
			case 'sa-east-1':
				$endpoint = 's3-'.$region.'.amazonaws.com';
				break;
			default:
				break;
		}
		if (isset($endpoint)) {
			$obj->setEndpoint($endpoint);
		}
	}

	function backup($backup_array) {

		global $updraftplus;

		if (!class_exists('S3')) require_once(UPDRAFTPLUS_DIR.'/includes/S3.php');

		$s3 = $this->getS3(UpdraftPlus_Options::get_updraft_option('updraft_s3_login'), UpdraftPlus_Options::get_updraft_option('updraft_s3_pass'));

		$bucket_name = untrailingslashit(UpdraftPlus_Options::get_updraft_option('updraft_s3_remote_path'));
		$bucket_path = "";
		$orig_bucket_name = $bucket_name;

		if (preg_match("#^([^/]+)/(.*)$#",$bucket_name,$bmatches)) {
			$bucket_name = $bmatches[1];
			$bucket_path = $bmatches[2]."/";
		}

		$region = @$s3->getBucketLocation($bucket_name);

		// See if we can detect the region (which implies the bucket exists and is ours), or if not create it
		if (!empty($region) || @$s3->putBucket($bucket_name, S3::ACL_PRIVATE)) {

			if (empty($region)) $region = $s3->getBucketLocation($bucket_name);
			$this->set_endpoint($s3, $region);

			$updraft_dir = $updraftplus->backups_dir_location().'/';

			foreach($backup_array as $key => $file) {

				// We upload in 5Mb chunks to allow more efficient resuming and hence uploading of larger files
				// N.B.: 5Mb is Amazon's minimum. So don't go lower or you'll break it.
				$fullpath = $updraft_dir.$file;
				$orig_file_size = filesize($fullpath);
				$chunks = floor($orig_file_size / 5242880);
				// There will be a remnant unless the file size was exactly on a 5Mb boundary
				if ($orig_file_size % 5242880 > 0 ) $chunks++;
				$hash = md5($file);

				$updraftplus->log("S3 upload ($region): $fullpath (chunks: $chunks) -> s3://$bucket_name/$bucket_path$file");

				$filepath = $bucket_path.$file;

				// This is extra code for the 1-chunk case, but less overhead (no bothering with transients)
				if ($chunks < 2) {
					if (!$s3->putObjectFile($fullpath, $bucket_name, $filepath)) {
						$updraftplus->log("S3 regular upload: failed ($fullpath)");
						$updraftplus->error("S3 Error: Failed to upload $file.");
					} else {
						$updraftplus->log("S3 regular upload: success");
						$updraftplus->uploaded_file($file);
					}
				} else {

					// Retrieve the upload ID
					$uploadId = get_transient("updraft_${hash}_uid");
					if (empty($uploadId)) {
						$s3->setExceptions(true);
						try {
							$uploadId = $s3->initiateMultipartUpload($bucket_name, $filepath);
						} catch (Exception $e) {
							$updraftplus->log('S3 error whilst trying initiateMultipartUpload: '.$e->getMessage().' (line: '.$e->getLine().', file: '.$e->getFile().')');
							$uploadId = false;
						}
						$s3->setExceptions(false);

						if (empty($uploadId)) {
							$updraftplus->log("S3 upload: failed: could not get uploadId for multipart upload ($filepath)");
							$updraftplus->error("S3 upload: getting uploadID for multipart upload failed - see log file for more details");
							continue;
						} else {
							$updraftplus->log("S3 chunked upload: got multipart ID: $uploadId");
							set_transient("updraft_${hash}_uid", $uploadId, UPDRAFT_TRANSTIME);
						}
					} else {
						$updraftplus->log("S3 chunked upload: retrieved previously obtained multipart ID: $uploadId");
					}

					$successes = 0;
					$etags = array();
					for ($i = 1 ; $i <= $chunks; $i++) {
						# Shorted to upd here to avoid hitting the 45-character limit
						$etag = get_transient("upd_${hash}_e$i");
						if (strlen($etag) > 0) {
							$updraftplus->log("S3 chunk $i: was already completed (etag: $etag)");
							$successes++;
							array_push($etags, $etag);
						} else {
							// Sanity check: we've seen a case where an overlap was truncating the file from underneath us
							if (filesize($fullpath) < $orig_file_size) {
								$updraftplus->error("S3 error: $key: chunk $i: file was truncated underneath us (orig_size=$orig_file_size, now_size=".filesize($fullpath).")");
							}
							$etag = $s3->uploadPart($bucket_name, $filepath, $uploadId, $fullpath, $i);
							if ($etag !== false && is_string($etag)) {
								$updraftplus->record_uploaded_chunk(round(100*$i/$chunks,1), "$i, $etag", $fullpath);
								array_push($etags, $etag);
								set_transient("upd_${hash}_e$i", $etag, UPDRAFT_TRANSTIME);
								$successes++;
							} else {
								$updraftplus->log("S3 chunk $i: upload failed");
								$updraftplus->error("S3 chunk $i: upload failed");
							}
						}
					}
					if ($successes >= $chunks) {
						$updraftplus->log("S3 upload: all chunks uploaded; will now instruct S3 to re-assemble");

						$s3->setExceptions(true);
						try {
							if ($s3->completeMultipartUpload ($bucket_name, $filepath, $uploadId, $etags)) {
								$updraftplus->log("S3 upload ($key): re-assembly succeeded");
								$updraftplus->uploaded_file($file);
							} else {
								$updraftplus->log("S3 upload ($key): re-assembly failed");
								$updraftplus->error("S3 upload ($key): re-assembly failed ($file)");
							}
						} catch (Exception $e) {
							$updraftplus->log("S3 re-assembly error ($key): ".$e->getMessage().' (line: '.$e->getLine().', file: '.$e->getFile().')');
							$updraftplus->error("S3 re-assembly error ($key): ".$e->getMessage().' (see log file for more)');
						}
						// Remember to unset, as the deletion code later reuses the object
						$s3->setExceptions(false);
					} else {
						$updraftplus->log("S3 upload: upload was not completely successful on this run");
					}
				}
			}
			$updraftplus->prune_retained_backups('s3', $this, array('s3_object' => $s3, 's3_orig_bucket_name' => $orig_bucket_name));
		} else {
			$updraftplus->log("S3 Error: Failed to create bucket $bucket_name.");
			$updraftplus->error("S3 Error: Failed to create bucket $bucket_name. Check your permissions and credentials.");
		}
	}

	function delete($file, $s3arr) {

		global $updraftplus;

		$s3 = $s3arr['s3_object'];
		$orig_bucket_name = $s3arr['s3_orig_bucket_name'];

		if (preg_match("#^([^/]+)/(.*)$#", $orig_bucket_name, $bmatches)) {
			$s3_bucket=$bmatches[1];
			$s3_uri = $bmatches[2]."/".$file;
		} else {
			$s3_bucket = $orig_bucket_name;
			$s3_uri = $file;
		}
		$updraftplus->log("S3: Delete remote: bucket=$s3_bucket, URI=$s3_uri");

		$s3->setExceptions(true);
		try {
			if (!$s3->deleteObject($s3_bucket, $s3_uri)) {
				$updraftplus->log("S3: Delete failed");
			}
		} catch  (Exception $e) {
			$updraftplus->log('S3 delete failed: '.$e->getMessage().' (line: '.$e->getLine().', file: '.$e->getFile().')');
		}
		$s3->setExceptions(false);

	}

	function download($file) {

		global $updraftplus;
		if(!class_exists('S3')) require_once(UPDRAFTPLUS_DIR.'/includes/S3.php');

		$s3 = $this->getS3(UpdraftPlus_Options::get_updraft_option('updraft_s3_login'), UpdraftPlus_Options::get_updraft_option('updraft_s3_pass'));

		$bucket_name = untrailingslashit(UpdraftPlus_Options::get_updraft_option('updraft_s3_remote_path'));
		$bucket_path = "";

		if (preg_match("#^([^/]+)/(.*)$#", $bucket_name, $bmatches)) {
			$bucket_name = $bmatches[1];
			$bucket_path = $bmatches[2]."/";
		}

		$region = @$s3->getBucketLocation($bucket_name);
		if (!empty($region)) {
			$this->set_endpoint($s3, $region);
			$fullpath = $updraftplus->backups_dir_location().'/'.$file;
			if (!$s3->getObject($bucket_name, $bucket_path.$file, $fullpath, true)) {
				$updraftplus->log("S3 Error: Failed to download $file. Check your permissions and credentials.");
				$updraftplus->error("S3 Error: Failed to download $file. Check your permissions and credentials.");
			}
		} else {
			$updraftplus->log("S3 Error: Failed to access bucket $bucket_name. Check your permissions and credentials.");
			$updraftplus->error("S3 Error: Failed to access bucket $bucket_name. Check your permissions and credentials.");
		}

	}

	public static function config_print_javascript_onready() {
		?>
		jQuery('#updraft-s3-test').click(function(){
			var data = {
				action: 'updraft_ajax',
				subaction: 'credentials_test',
				method: 's3',
				nonce: '<?php echo wp_create_nonce('updraftplus-credentialtest-nonce'); ?>',
				apikey: jQuery('#updraft_s3_apikey').val(),
				apisecret: jQuery('#updraft_s3_apisecret').val(),
				path: jQuery('#updraft_s3_path').val()
			};
			jQuery.post(ajaxurl, data, function(response) {
					alert('Settings test result: ' + response);
			});
		});
		<?php
	}

	public static function config_print() {

	?>
		<tr class="updraftplusmethod s3">
			<td></td>
			<td><img src="https://d36cz9buwru1tt.cloudfront.net/Powered-by-Amazon-Web-Services.jpg" alt="Amazon Web Services"><p><em>Amazon S3 is a great choice, because UpdraftPlus supports chunked uploads - no matter how big your blog is, UpdraftPlus can upload it a little at a time, and not get thwarted by timeouts.</em></p></td>
		</tr>
		<tr class="updraftplusmethod s3">
		<th></th>
		<td>
			<p>Get your access key and secret key <a href="http://aws.amazon.com/console/">from your AWS console</a>, then pick a (globally unique - all Amazon S3 users) bucket name (letters and numbers) (and optionally a path) to use for storage. This bucket will be created for you if it does not already exist.</p>
		</td></tr>
		<tr class="updraftplusmethod s3">
			<th>S3 access key:</th>
			<td><input type="text" autocomplete="off" style="width: 292px" id="updraft_s3_apikey" name="updraft_s3_login" value="<?php echo htmlspecialchars(UpdraftPlus_Options::get_updraft_option('updraft_s3_login')) ?>" /></td>
		</tr>
		<tr class="updraftplusmethod s3">
			<th>S3 secret key:</th>
			<td><input type="text" autocomplete="off" style="width: 292px" id="updraft_s3_apisecret" name="updraft_s3_pass" value="<?php echo htmlspecialchars(UpdraftPlus_Options::get_updraft_option('updraft_s3_pass')); ?>" /></td>
		</tr>
		<tr class="updraftplusmethod s3">
			<th>S3 location:</th>
			<td>s3://<input type="text" style="width: 292px" name="updraft_s3_remote_path" id="updraft_s3_path" value="<?php echo htmlspecialchars(UpdraftPlus_Options::get_updraft_option('updraft_s3_remote_path')); ?>" /></td>
		</tr>
		<tr class="updraftplusmethod s3">
		<th></th>
		<td><p><button id="updraft-s3-test" type="button" class="button-primary" style="font-size:18px !important">Test S3 Settings</button></p></td>
		</tr>
	<?php
	}

	public static function credentials_test() {

		if (empty($_POST['apikey'])) {
			echo "Failure: No API key was given.";
			return;
		}
		if (empty($_POST['apisecret'])) {
			echo "Failure: No API secret was given.";
			return;
		}

		$key = $_POST['apikey'];
		$secret = $_POST['apisecret'];
		$path = $_POST['path'];

		if (preg_match("#^([^/]+)/(.*)$#", $path, $bmatches)) {
			$bucket = $bmatches[1];
			$path = $bmatches[2];
		} else {
			$bucket = $path;
			$path = "";
		}

		if (empty($bucket)) {
			echo "Failure: No bucket details were given.";
			return;
		}

		if (!class_exists('S3')) require_once(UPDRAFTPLUS_DIR.'/includes/S3.php');
		$s3 = new S3($key, $secret);

		$location = @$s3->getBucketLocation($bucket);
		if ($location) {
			$bucket_exists = true;
			$bucket_verb = "accessed (Amazon region: $location)";
			$bucket_region = $location;
		} else {
			$try_to_create_bucket = @$s3->putBucket($bucket, S3::ACL_PRIVATE);
			if ($try_to_create_bucket) {
				$bucket_verb = 'created';
				$bucket_exists = true;
			} else {
				echo "Failure: We could not successfully access or create such a bucket. Please check your access credentials, and if those are correct then try another bucket name (as another S3 user may already have taken your name).";
			}
		}

		if (isset($bucket_exists)) {
			$try_file = md5(rand());
			self::set_endpoint($s3, $location);
			if (!$s3->putObjectString($try_file, $bucket, $path.$try_file)) {
				echo "Failure: We successfully $bucket_verb the bucket, but the attempt to create a file in it failed.";
			} else {
				echo "Success: we $bucket_verb the bucket, and were able to create files within it.";
				@$s3->deleteObject($bucket, $path.$try_file);
			}
		}

	}

}
?>
