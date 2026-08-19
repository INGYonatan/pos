<?php
class HelperResponseModel
{
  public $status;
  public $message;
  public $data;

  public function __construct($status = "error", $message = "Error inesperado, intentalo nuevamente", $data = [])
  {
    $this->status   = $status;
    $this->message  = $message;
    $this->data     = $data;
  }
}
