/**
 * @provides javelin-behavior-role-create
 * @requires javelin-behavior
 *           javelin-dom
 *           javelin-stratcom
 *           javelin-workflow
 */

JX.behavior('role-create', function(config) {

  JX.Stratcom.listen(
    'click',
    'role-create',
    function(e) {
      JX.Workflow.newFromLink(e.getTarget())
        .setHandler(function(r) {
          var node = JX.$(config.tokenizerID);
          var tokenizer = JX.Stratcom.getData(node).tokenizer;
          tokenizer.addToken(r.phid, r.name);
        })
        .start();

      e.kill();
    });

});
