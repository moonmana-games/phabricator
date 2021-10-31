<?php

$role_table = new PhabricatorRole();
$table_name = $role_table->getTableName();
$conn_w = $role_table->establishConnection('w');
$slug_table_name = id(new PhabricatorRoleSlug())->getTableName();
$time = PhabricatorTime::getNow();

echo pht('Migrating roles to slugs...')."\n";
foreach (new LiskMigrationIterator($role_table) as $role) {
  $id = $role->getID();

  echo pht('Migrating role %d...', $id)."\n";

  $slug_text = PhabricatorSlug::normalizeRoleSlug($role->getName());
  $slug = id(new PhabricatorRoleSlug())
    ->loadOneWhere('slug = %s', $slug_text);

  if ($slug) {
    echo pht('Already migrated %d... Continuing.', $id)."\n";
    continue;
  }

  queryfx(
    $conn_w,
    'INSERT INTO %T (rolePHID, slug, dateCreated, dateModified) '.
    'VALUES (%s, %s, %d, %d)',
    $slug_table_name,
    $role->getPHID(),
    $slug_text,
    $time,
    $time);
  echo pht('Migrated %d.', $id)."\n";
}

echo pht('Done.')."\n";
