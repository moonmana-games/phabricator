<?php

$table = new PhrictionDocument();
$conn_w = $table->establishConnection('w');

echo pht('Populating Phriction policies.')."\n";

$default_view_policy = PhabricatorPolicies::POLICY_USER;
$default_edit_policy = PhabricatorPolicies::POLICY_USER;

foreach (new LiskMigrationIterator($table) as $doc) {
  $id = $doc->getID();

  if ($doc->getViewPolicy() && $doc->getEditPolicy()) {
    echo pht('Skipping document %d; already has policy set.', $id)."\n";
    continue;
  }

  $new_view_policy = $default_view_policy;
  $new_edit_policy = $default_edit_policy;

  // If this was previously a magical role wiki page (under roles/, but
  // not roles/ itself) we need to apply the role policies. Otherwise,
  // apply the default policies.
  $slug = $doc->getSlug();
  $slug = PhabricatorSlug::normalize($slug);
  $prefix = 'roles/';
  if (($slug != $prefix) && (strncmp($slug, $prefix, strlen($prefix)) === 0)) {
    $parts = explode('/', $slug);

    $role_slug = $parts[1];
    $role_slug = PhabricatorSlug::normalizeProjectSlug($role_slug);

    $role_slugs = array($role_slug);
    $role = id(new PhabricatorRoleQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withSlugs($role_slugs)
      ->executeOne();

    if ($role) {
      $view_policy = nonempty($role->getViewPolicy(), $default_view_policy);
      $edit_policy = nonempty($role->getEditPolicy(), $default_edit_policy);

      $new_view_policy = $view_policy;
      $new_edit_policy = $edit_policy;
    }
  }

  echo pht('Migrating document %d to new policy...', $id)."\n";

  queryfx(
    $conn_w,
    'UPDATE %R SET viewPolicy = %s, editPolicy = %s
      WHERE id = %d',
    $table,
    $new_view_policy,
    $new_edit_policy,
    $id);
}

echo pht('Done.')."\n";
