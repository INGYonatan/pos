<?php
function sendApiResponse($response, $statusCode = 200, $message = null, $data = null)
{
  /**
   * @var ApiResponseModel $response
   */
  if ($message  !== null) $response->message  = $message;
  if ($data     !== null) $response->data     = $data;

  // Set the content type and status code
  header("Content-Type: application/json; charset=UTF-8");
  if ($statusCode < 100 || $statusCode >= 600) {
    $statusCode = 500; // Default to 500 Internal Server Error for invalid status codes
  }

  if ($statusCode >= 400) {
    $response->status = "error";
  } else {
    $response->status = "success";
  }

  http_response_code($statusCode);
  echo json_encode($response);
  die;
}
