<?php

echo pht('Updating role datasource tokens...')."\n";

foreach (new LiskMigrationIterator(new PhabricatorRole()) as $role) {
  $name = $role->getName();
  echo pht("Updating role '%d'...", $name)."\n";
  $role->updateDatasourceTokens();
}

echo pht('Done.')."\n";
