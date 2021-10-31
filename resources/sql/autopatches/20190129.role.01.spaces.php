<?php

// See PHI1046. The "spacePHID" column for milestones may have fallen out of
// sync; correct all existing values.

$table = new PhabricatorRole();
$conn = $table->establishConnection('w');
$table_name = $table->getTableName();

foreach (new LiskRawMigrationIterator($conn, $table_name) as $role_row) {
  queryfx(
    $conn,
    'UPDATE %R SET spacePHID = %ns
      WHERE parentRolePHID = %s AND milestoneNumber IS NOT NULL',
    $table,
    $role_row['spacePHID'],
    $role_row['phid']);
}
