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
    $this->url =  CRM_Utils_File::addTrailingSlash($config->getMoodleURL(), '/') . 'webservice/rest/server.php';
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

  public function createCourse($id, $title, $startDate) {
    $apiParams = [
      'courses[0][fullname]' => $title,
      'courses[0][shortname]' => $title,
      'courses[0][categoryid]' => 1,
      'courses[0][idnumber]' => $id,
      'courses[0][startdate]' => $startDate,
    ];

    $response = $this->sendRequest('core_course_create_courses', $apiParams);

    // return the course id
    return $response[0]['id'];
  }

  public function updateCourseCategories($categories) {

  }

  private function sendRequest($apiFunc, $apiParams) {
    $searchArgs = [
      'wstoken=' . $this->token,
      'wsfunction=' . $apiFunc,
      'moodlewsrestformat=json',
    ];

    // add the extra params
    foreach ($apiParams as $k => $v) {
      $searchArgs[] = "$k=$v";
    }

    // send the request
    list($status, $response) = $this->httpClient->get($this->url . '?' . implode('&', $searchArgs));
    if ($status == 'ok') {
      return json_decode($response);
    }
    else {
      throw new Exception('Request failed');
    }
  }

}
