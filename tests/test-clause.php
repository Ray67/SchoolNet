
<?php 

require 'lib\Clause.php';

$clauses[] = new Clause('id', 'IN', ['Alpha','Beta']);
$clauses[] = new Clause('id', Clause::_IN, ['Alpha','Beta']);
$clauses[] = new Clause('id', Clause::_IN, ['Omega']);
$clauses[] = new Clause('id', '=', 2);
$clauses[] = new Clause($clauses[0], 'and', $clauses[1]);

foreach ($clauses as $key =>$clause)
{
    echo $clause .'<br/>';
}

?>
