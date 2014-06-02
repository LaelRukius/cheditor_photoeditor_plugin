<?php
require_once("../imageUpload/_config.php");
header('Content-Type:text/html;charset=UTF-8');
// ---------------------------------------------------------------------------

/**
cheditor 사진업로더 이미지 온라인편집기
===========================
* https://github.com/LaelRukius/cheditor_photoeditor_plugin

@이 파일을 호출하는 경우는 2가지.
1) CH에디터 to 네이버에디터
2) 네이버에디터 to CH에디터
*/

if(function_exists('curl_init')==FALSE){
	alert_close('php curl 모듈이 설치되어야 사용가능합니다.');
}

function remove_rand_var($fileurl){
	$tmp_arr = explode('?rand',$fileurl);
	return $tmp_arr[0];
}

function chplugin_get_filename_by_fileurl($fileurl){
	$m = array();
	// 파일의 경로에서 파일명만 얻어낸다. \ 나 / 는 제거된다.
	preg_match('/[0-9a-z_]+\.(gif|png|jpe?g)$/i', $fileurl, $m);
	$filename = $m[0];

	return $filename;
}

function chplugin_is_valid_userfile($fileurl){
	/**
	@권한 체크: true 권한있음, false 권한없음
	*/
	
	$filename = chplugin_get_filename_by_fileurl($fileurl);

	// 파일의 아이피 부분만 잘라내서 자신의 아이피인지 비교한다.
	preg_match('#([0-9a-f]+)_([0-9]+)_([a-z]+)\.(gif|png|jpe?g)$#i', $filename, $m);
	$md5ip = $m[1];

	if ($md5ip == md5($_SERVER['REMOTE_ADDR'])) {
		return true;
	}
	else {
		return false;
	}

}

function chplugin_get_remote_image($tUrl){
	$ch = curl_init();
	 
	curl_setopt($ch, CURLOPT_URL, $tUrl);
	curl_setopt($ch, CURLOPT_REFERER, $rUrl);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; .NET CLR 1.1.4322; .NET CLR 3.0.04506.30) '); //브라우저 종류 - Explorer 7.0
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$f = curl_exec($ch);
	curl_close($ch);

	return $f;
}

$import = remove_rand_var(trim($_GET['import']));
$self_id = trim($_GET['self_id']);
$file = trim($_GET['file']);

if($import != ''){// 1) CH에디터 to 네이버에디터
	
	if(chplugin_is_valid_userfile($import)){
		$nhn_editor_url = 'http://s.lab.naver.com/pe/service?exportTo='.urlencode(($_SERVER['HTTPS']=='on'?'https://':'http://').$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']). '&exportField=file&import='.urlencode($import).'&exportTitle='.urlencode('웹에디터');
		$_SESSION['cheditor_modify_image_url'] = $import;
		$_SESSION['cheditor_modify_self_id'] = $self_id;
		goto_url($nhn_editor_url);
	}
	else{
		//그누보드는 이미지 업로드시 파일명에 아이피를 기록합니다. 해당 아이피와 현재 아이피를 비교합니다.
		alert_close('해당 이미지를 편집할 권한이 없습니다. 확인 후 다시 이용해주세요.');
	}

	exit;
}

if($file != ''){// 2) 네이버에디터 to CH에디터

	$import = $_SESSION['cheditor_modify_image_url'];
	$self_id = $_SESSION['cheditor_modify_self_id'];

	$nhn_editor_url = 'http://s.lab.naver.com/pe/service?import='.urlencode($file);//이 서버에 저장이 허용되지 않을경우 에디터로 다시 되돌린다.

	if(strpos($file,'s.lab.naver.com') === FALSE || $import == '' || chplugin_is_valid_userfile($import) == FALSE){//유효한 URL이 아니거나 인증파일 정보가 없거나 사용자파일이 아니면
		alert('인증오류가 발생하였습니다. 네이버 에디터의 [PC저장]을 이용해 주시기 바랍니다.',$nhn_editor_url);
	}
	else{
		$image_data = chplugin_get_remote_image($file);
		if($image_data == FALSE || $image_data == ''){
			//원격지 이미지를 불러올 수 없음.
			alert('편집한 이미지를 서버로 불러올 수 없었습니다. [PC저장]을 이용해 주시기 바랍니다.',$nhn_editor_url);
		}
		else{

			$filename = chplugin_get_filename_by_fileurl($import);

			$tmp_arr = explode('/data/cheditor4/',$import);
			$file_savepath = $tmp_arr[1];
			if($file_savepath==''){
				alert_close('불량 데이터가 수신되어서 작업이 종료됩니다. 잠시 후 다시 이용해주세요.');//불량데이터
			}
		
			$file_savepath = "{$g4['path']}/data/{$g4['cheditor4']}/".$file_savepath;

			if(!file_exists($file_savepath)){//파일이 존재하지 않음
				alert('일시적 장애가 발생했습니다. 네이버 에디터의 [PC저장]을 이용해 주시기 바랍니다.',$nhn_editor_url);
			}

			//타겟파일의 최종수정시간과 현재시간의 차이가 1일이상이면 수정하지 않음
			$m_time = filemtime($file_savepath);//파일 최종수정시간
			$now_time = strtotime("now");//현재 시간
			$diff_time = $now_time-$m_time;

			if($diff_time > 86400){
				alert('일시적 장애가 발생했습니다. 네이버 에디터의 [PC저장]을 이용해 주시기 바랍니다.',$nhn_editor_url);
			}



			//STEP 1. 파일을 임시저장하여 이미지 파일인지 체크

			$tmp_savepath = SAVE_DIR . '/__tmp__' .rand(10000,20000). $filename;//우선 저장
			$handle = fopen($tmp_savepath, 'a');
			if($handle==''){//파일 핸들러가 안잡히면
				alert('편집된 이미지를 수신하였으나, 파일을 저장할 수 없었습니다. 네이버 에디터의 [PC저장]을 이용해 주시기 바랍니다.',$nhn_editor_url);
			}
			if(fwrite($handle, $image_data) === FALSE){//쓰기가 안되면
				alert('편집된 이미지를 수신하였으나, 파일을 저장할 수 없었습니다. 네이버 에디터의 [PC저장]을 이용해 주시기 바랍니다.',$nhn_editor_url);
			}
			fclose($handle);

			$image_meta_data = getimagesize($tmp_savepath);
			@unlink($tmp_savepath);

			if(!is_numeric($image_meta_data[0]) || !is_numeric($image_meta_data[1]) || $image_meta_data[0]=='0' || $image_meta_data[1]=='0' ){
				alert_close('전송된 데이터가 이미지 데이터가 아닙니다. 작업이 종료됩니다.',$nhn_editor_url);
			}


			//STEP 2. 이미지 데이터로 검증되었으므로 기존파일 대체

			$handle = fopen($file_savepath, 'w');
			if($handle==''){//파일 핸들러가 안잡히면
				alert('편집된 이미지를 수신하였으나, 파일을 저장할 수 없었습니다. 네이버 에디터의 [PC저장]을 이용해 주시기 바랍니다.',$nhn_editor_url);
			}
			if(fwrite($handle, $image_data) === FALSE){//쓰기가 안되면
				alert('편집된 이미지를 수신하였으나, 파일을 저장할 수 없었습니다. 네이버 에디터의 [PC저장]을 이용해 주시기 바랍니다.',$nhn_editor_url);
			}
			fclose($handle);

			//success -> 에디터에 반영
			echo "
				<script type=\"text/javascript\">
				alert('이미지 편집이 완료되었습니다.');
				try{
					opener.imageCompletedList['{$self_id}']['width'] = '{$image_meta_data[0]}';
					opener.imageCompletedList['{$self_id}']['height'] = '{$image_meta_data[1]}';
					opener.imageCompletedList['{$self_id}']['fileUrl'] += '?rand='+Math.random();
					opener.document.getElementById('{$self_id}').innerHTML = opener.document.getElementById('{$self_id}').innerHTML.replace('{$filename}','{$filename}?rand='+Math.random());
					window.close();
				}
				catch(e){
					window.close();
					//alert(e);
				}
				window.close();
				</script>
			";
			
			exit;
		}

		

	}
	
	exit;
}


