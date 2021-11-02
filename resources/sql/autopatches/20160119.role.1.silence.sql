/* PhabricatorObjectHasUnsubscriberEdgeType::EDGECONST = 23 */
/* PhabricatorRoleSilencedEdgeType::EDGECONST = 668 */

/* This is converting existing unsubscribes into disabled mail. */

INSERT IGNORE INTO {$NAMESPACE}_role.edge (src, type, dst, dateCreated)
  SELECT src, 668, dst, dateCreated FROM {$NAMESPACE}_role.edge
  WHERE type = 23;
