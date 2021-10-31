<?php

// Populate the newish `hasWorkboard` column for roles with workboard.
// Set the default menu item to "Workboard" for roles which used to have
// that default.

$role_table = new PhabricatorRole();
$conn_w = $role_table->establishConnection('w');

$panel_table = id(new PhabricatorProfileMenuItemConfiguration());
$panel_conn = $panel_table->establishConnection('w');

foreach (new LiskMigrationIterator($role_table) as $role) {
  $columns = queryfx_all(
    $conn_w,
    'SELECT * FROM %T WHERE rolePHID = %s',
    id(new PhabricatorRoleColumn())->getTableName(),
    $role->getPHID());

  // This role has no columns, so we don't need to change anything.
  if (!$columns) {
    continue;
  }

  // This role has columns, so set its workboard flag.
  queryfx(
    $conn_w,
    'UPDATE %T SET hasWorkboard = 1 WHERE id = %d',
    $role->getTableName(),
    $role->getID());

  // Try to set the default menu item to "Workboard".
  $config = queryfx_all(
    $panel_conn,
    'SELECT * FROM %T WHERE profilePHID = %s',
    $panel_table->getTableName(),
    $role->getPHID());

  // There are already some settings, so don't touch them.
  if ($config) {
    continue;
  }

  queryfx(
    $panel_conn,
    'INSERT INTO %T
      (phid, profilePHID, panelKey, builtinKey, visibility, panelProperties,
        panelOrder, dateCreated, dateModified)
      VALUES (%s, %s, %s, %s, %s, %s, %d, %d, %d)',
    $panel_table->getTableName(),
    $panel_table->generatePHID(),
    $role->getPHID(),
    PhabricatorRoleWorkboardProfileMenuItem::MENUITEMKEY,
    PhabricatorRole::ITEM_WORKBOARD,
    PhabricatorProfileMenuItemConfiguration::VISIBILITY_DEFAULT,
    '{}',
    2,
    PhabricatorTime::getNow(),
    PhabricatorTime::getNow());
}
