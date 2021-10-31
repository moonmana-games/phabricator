<?php

$app = PhabricatorApplication::getByClass('PhabricatorRoleApplication');

$view_policy = $app->getPolicy(RoleDefaultViewCapability::CAPABILITY);
$edit_policy = $app->getPolicy(RoleDefaultEditCapability::CAPABILITY);
$join_policy = $app->getPolicy(RoleDefaultJoinCapability::CAPABILITY);

$table = new PhabricatorRole();
$conn_w = $table->establishConnection('w');

queryfx(
  $conn_w,
  'UPDATE %T SET viewPolicy = %s WHERE viewPolicy IS NULL',
  $table->getTableName(),
  $view_policy);

queryfx(
  $conn_w,
  'UPDATE %T SET editPolicy = %s WHERE editPolicy IS NULL',
  $table->getTableName(),
  $edit_policy);

queryfx(
  $conn_w,
  'UPDATE %T SET joinPolicy = %s WHERE joinPolicy IS NULL',
  $table->getTableName(),
  $join_policy);
