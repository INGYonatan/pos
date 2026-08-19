<?php
class ModelResponse
{
  public $status;
  public $message;
  public $data;

  public function __construct()
  {
    $this->status   = "error";
    $this->message  = "Error inesperado, intentalo nuevamente";
    $this->data     = new stdClass();
  }
}
