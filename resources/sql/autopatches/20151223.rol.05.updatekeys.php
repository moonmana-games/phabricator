<?php

$table = new PhabricatorRole();
$conn_w = $table->establishConnection('w');

foreach (new LiskMigrationIterator($table) as $role) {
  $path = $role->getRolePath();
  $key = $role->getRolePathKey();

  if (strlen($path) && ($key !== "\0\0\0\0")) {
    continue;
  }

  $path_key = PhabricatorHash::digestForIndex($role->getPHID());
  $path_key = substr($path_key, 0, 4);

  queryfx(
    $conn_w,
    'UPDATE %T SET rolePath = %s, rolePathKey = %s WHERE id = %d',
    $role->getTableName(),
    $path_key,
    $path_key,
    $role->getID());
}
