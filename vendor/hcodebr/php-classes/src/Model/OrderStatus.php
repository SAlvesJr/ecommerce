<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class OrderStatus extends Model{
	
	const EM_ABERTO = 1;
	const AGUARDADNO_PAGAMENTO = 2;
	const PAGO = 3;
	const ENTREGUE = 4;

}
 ?>