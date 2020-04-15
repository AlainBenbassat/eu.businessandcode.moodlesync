<?php

class CRM_Moodlesync_API {
  private $config;
  private $url;
  private $token;
  private $httpClient;

  /**
   * CRM_Moodlesync_API constructor.
   *
   * @param $config \CRM_Moodlesync_Config
   */
  public function __construct($config) {
    $this->config = $config;
    $this->url =  $config->getMoodleURL() . 'webservice/rest/server.php';
    $this->token = $config->getMoodleToken();

    $this->httpClient = new CRM_Utils_HttpClient();
  }

  public function testConnection() {
    // test the connection by retrieving a user by (fake) email address
    $params = [
      'criteria[0][key]' => 'email',
      'criteria[0][value]' => 'test@test.com',
    ];
    $this->sendRequest('core_user_get_users', $params);
  }

  public function getCourseCategories() {
    $apiParams = [];

    $response = $this->sendRequest('core_course_get_categories', $apiParams);

    // return the categories
    return $response;
  }

  public function createCourse($id, $title, $startDate, $endDate, $categoryId) {
    $apiParams = [
      'courses[0][fullname]' => $title,
      'courses[0][shortname]' => $title,
      'courses[0][categoryid]' => $categoryId,
      'courses[0][idnumber]' => $id,
      'courses[0][startdate]' => strtotime($startDate),
      'courses[0][enddate]' => strtotime($endDate),
    ];

    $response = $this->sendRequest('core_course_create_courses', $apiParams);

    // return the course id
    return $response[0]->id;
  }

  public function createUser($id, $firstName, $lastName, $email) {
    $apiParams = [
      'users[0][username]' => $email,
      'users[0][firstname]' => $firstName,
      'users[0][lastname]' => $lastName,
      'users[0][email]' => $email,
      'users[0][idnumber]' => $id,
      'users[0][password]' => 'A!-' . md5(uniqid()), // just generate a random password with upper case letter and non-alpha char
    ];

    $response = $this->sendRequest('core_user_create_users', $apiParams);

    // return the course id
    return $response[0]->id;
  }

  public function createEnrolment($roleId, $userId, $courseId) {
    $apiParams = [
      'enrolments[0][roleid]' => $roleId,
      'enrolments[0][userid]' => $userId,
      'enrolments[0][courseid]' => $courseId,
    ];

    $response = $this->sendRequest('enrol_manual_enrol_users', $apiParams);

    // unfortunately, the api does not return an enrolment id
    return '';
  }

  private function sendRequest($apiFunc, $apiParams) {
    $searchArgs = [
      'wstoken=' . $this->token,
      'wsfunction=' . $apiFunc,
      'moodlewsrestformat=json',
    ];

    // add the extra params
    foreach ($apiParams as $k => $v) {
      $searchArgs[] = "$k=" . urlencode($v);
    }

    // send the request
    $url = $this->url . '?' . implode('&', $searchArgs);

    list($status, $response) = $this->httpClient->get($url);
    if ($status == 'ok') {
      $decodedResponse = json_decode($response);
      if ($decodedResponse == NULL) {
        throw new Exception("Response = $response, but expected JSON object");
      }
      elseif (property_exists($decodedResponse, 'exception')) {
        throw new Exception('MoodleSync Error: API=' . $apiFunc . ', Exception='. $decodedResponse->exception . ', Message=' . $decodedResponse->message);
      }
      else {
        // success
        return $decodedResponse;
      }
    }
    else {
      throw new Exception('Request failed');
    }
  }

}
