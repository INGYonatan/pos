<?php
class APiResponseModel
{
  public $status;
  public $message;
  public $data;

  public function __construct()
  {
    $this->status   = "error";
    $this->message  = "Error inesperado, intetalo nuevamente";
    $this->data     = [];
  }
}
