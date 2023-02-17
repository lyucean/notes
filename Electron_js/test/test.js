var assert = require('assert');
var helpers = require("../app/helper");


describe('HelloTest', function() {
  describe('test sum(2,3)', function() {
    it('Должен вернуть 5, если всё пошло по лану', function() {
      assert.equal(helpers.sum(2, 3), 5);
    });
  });
});
