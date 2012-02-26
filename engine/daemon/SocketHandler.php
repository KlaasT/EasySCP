<?php
class SocketHandler {

	private $Socket;
	private $Socket_Bind;
	private $Client;

	public function Create($Interface, $Type, $Protocol) {
		$this->Socket = socket_create($Interface, $Type, $Protocol);
		if ($this->Socket !== false)
		{
			return true;
		} else {
			return false;
		}
	}

	public function Bind($MyAddress="127.0.0.1", $MyPort) {
		$this->Socket_Bind = socket_bind($this->Socket, $MyAddress, $MyPort);
		if ($this->Socket_Bind !== false)
		{
			return true;
		} else {
			return false;
		}
	}

	public function Listen()
	{
		if ((socket_listen($this->Socket)) !== FALSE)
		{
			return true;
		} else {
			return false;
		}
	}

	public function Accept()
	{
		if ($this->Client = @socket_accept($this->Socket))
		{

			return $this->Client;
		} else {
			return false;
		}
	}

	public function Write($MSG)
	{
		return socket_write($this->Client, $MSG);
	}

	public function Read()
	{
		return @socket_read($this->Client, 1024, PHP_NORMAL_READ);
	}

	public function CloseClient($Status)
	{
		global $StatusMap;

		$Close_MSG = $Status." ".$StatusMap[$Status]."\n";
		$this->Write($Close_MSG);
		return socket_close($this->Client);
	}

	public function Close($Status)
	{
		$this->CloseClient($Status);
		return socket_close($this->Socket);
	}

	public function Block($block)
	{
	    if ($block === true){
		// Block socket type
		socket_set_block($this->Socket);
	    } else {
		// Non block socket type
		socket_set_nonblock($this->Socket);
	    }
	}
}
?>