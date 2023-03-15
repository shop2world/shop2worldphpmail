<?php
// 한글 인코딩 설정
header('Content-Type: text/html; charset=utf-8');

// 문의 양식 제출 처리 함수
function submitContactForm() {
  // POST 데이터 추출
  $name = $_POST['name'];
  $email = $_POST['email'];
  $message = $_POST['message'];

  // reCAPTCHA v3 검증
  $recaptcha_token = $_POST['recaptcha_response'];
  $user_ip = $_SERVER['REMOTE_ADDR'];
  $recaptcha_result = verifyRecaptcha($recaptcha_token, $user_ip);

  if ($recaptcha_result['success'] && $recaptcha_result['score'] >= 0.5) {
    // reCAPTCHA 검증 통과 시, 이메일 전송 처리
    $to = "example@example.com"; // 수신 이메일 주소
    $subject = "Contact Form Submission from $name";
    $body = "Name: $name\n\nEmail: $email\n\nMessage: $message";
    $headers = "From: $email";

    if (mail($to, $subject, $body, $headers)) {
      // 이메일 전송 성공 시, 성공 메시지 출력 및 페이지 이동
      echo '<script>alert("Thank you. Your message has been sent. We will get back to you as soon as possible."); setTimeout(function(){ window.location.href = "' . $_SERVER['HTTP_REFERER'] . '"; }, 1000);</script>';
    } else {
      // 이메일 전송 실패 시, 오류 메시지 출력
      $response = array('success' => false, 'message' => 'Failed to send message.');
    }
  } else {
    // reCAPTCHA 검증 실패 시, 오류 메시지 출력
    $response = array('success' => false, 'message' => 'reCAPTCHA verification failed.');
  }
  
  if (isset($response) && !$response['success']) {
    // JSON 응답 생성 및 출력
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
  }
}

// reCAPTCHA v3 검증 함수
function verifyRecaptcha($token, $ip) {
  // cURL 세션 초기화 및 설정
  $ch = curl_init();
  curl_setopt_array($ch, array(
    CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(array(
      'secret' => 'YOUR-SECRET_KEY', // 비밀 키 값
      'response' => $token,
      'remoteip' => $ip,
    )),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
  ));

  // reCAPTCHA 검증 요청 수행 및 응답 추출
  $response = curl_exec($ch);
  $response_data = json_decode($response, true);

  // cURL 세션 종료
  curl_close($ch);

  return $response_data;
}

// 문의 양식 제출 처리 함수 호출
submitContactForm();
?>
