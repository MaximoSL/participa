$(document).ready(function () {

  var diff_generated = false;

  function getDiff(enabled) {

    if(diff_generated !== true) {

      var dmp = new diff_match_patch();
      dmp.Diff_Timeout = 1;
      dmp.Diff_EditCost = 5;

      $('.diff_layout').each(function(){

        var $element = $(this);
        var $text1 = $element.find('.text1');
        var $text2 = $element.find('.text2');
        var $inline_diff_result = $element.find('.inline_diff_result');
        var $side_diff_result = $element.find('.side_diff_result');

        var text1 = $text1.html();
        var text2 = $text2.html();

        // Inline Diff
        var ds = diffString(text1, text2);
        $inline_diff_result.html(ds);

        // Side by Side Diff
        $side_diff_result_text_1 = $element.find('.side_diff_result.side_text_1');
        $side_diff_result_text_2 = $element.find('.side_diff_result.side_text_2');
        $side_diff_result_text_1.html(ds);
        $side_diff_result_text_2.html(ds);
        $side_diff_result_text_1.find('ins').remove();
        $side_diff_result_text_2.find('del').remove();

        // Hide original texts
        $text1.hide();
        $text2.hide();

        diff_generated = true;
      });

    }

    $('.diff_result').hide();

    $('.diff_layout').each(function(){
      var $diff_result_enabled = $(this).find('.'+enabled);
      $diff_result_enabled.show();
    });
  }

  function getInlineDiff() {
    $('.side-diff-visible').hide();
    getDiff('inline_diff_result');
  }

  function getSideDiff() {
    $('.side-diff-visible').show();
    getDiff('side_diff_result');
  }

  $('#inline-diff-layout-toggle').click(function(e){
    e.preventDefault();
    getInlineDiff();
  });

  $('#side-diff-layout-toggle').click(function(e){
    e.preventDefault();
    getSideDiff();
  });

  getSideDiff();

});
