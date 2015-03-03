<?php

include_once(dirname(__FILE__).'/../../classes/partner.php');
class YamodulepokupkiModuleFrontController extends ModuleFrontController
{
	public $display_header = false;
	public $display_column_left = false;
	public $display_column_right = false;
	public $display_footer = false;
	public $ssl = true;

	public function postProcess()
	{
		parent::postProcess();
		$type = Tools::getValue('type');
		$func = Tools::getValue('func');
		$arr = array($type, $func);
		$arr = array_merge($arr, $_REQUEST);
		$dd = serialize($arr);
		$this->module->log_save('pokupki '.$dd);
		$key = Tools::getValue('auth-token');
		$sign = Configuration::get('YA_POKUPKI_TOKEN');
		if (Tools::strtoupper($sign) != Tools::strtoupper($key))
		{
			header('HTTP/1.0 404 Not Found');
			echo '<h1>Wrong token</h1>';
			exit;
		}
		else
		{
			$json = file_get_contents("php://input");
			$this->module->log_save('pokupki'.$json);
			if (!$json){
				header('HTTP/1.0 404 Not Found');
				echo '<h1>No data posted</h1>';
				exit;
			}
			else
			{
				header('Content-type:application/json;  charset=utf-8');
				$partner = new Partner();
				$data = Tools::jsonDecode($json);
				if($type == 'cart')
					$response = $partner->requestItems($data);
				elseif($type == 'order')
				{
					if($func == 'accept')
						$response = $partner->orderAccept($data);
					elseif($func == 'status')
						$partner->alertOrderStatus($data);
				}
				else
				{
					header('HTTP/1.0 404 Not Found');
					echo '<h1>Wrong controller</h1>';
					exit;
				}
			}
		}
	}
}