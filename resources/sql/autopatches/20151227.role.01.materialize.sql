/* PhabricatorRoleRoleHasMemberEdgeType::EDGECONST = 646 */
/* PhabricatorRoleMaterializedMemberEdgeType::EDGECONST = 615 */

INSERT IGNORE INTO {$NAMESPACE}_role.edge (src, type, dst, dateCreated)
  SELECT src, 615, dst, dateCreated FROM {$NAMESPACE}_role.edge
  WHERE type = 646;
