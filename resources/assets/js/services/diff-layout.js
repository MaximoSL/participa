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
        // var d = dmp.diff_main(text1, text2);
        // dmp.diff_cleanupEfficiency(d);
        // var ds = dmp.diff_prettyHtml(d);
        // ds = ds.replace(/<span>&para;/g, '<span>');
        // ds = ds.replace('<br>', '');
        $inline_diff_result.html(ds);

        // Side diff
        // $side_diff_result.mergely({
        //   cmsettings: { readOnly: true, lineNumbers: false },
        //   lhs: function(setValue) {
        //     setValue(text1);
        //   },
        //   rhs: function(setValue) {
        //     setValue(text2);
        //   }
        // });

        //$text1.hide();
        //$text2.hide();

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

  getInlineDiff();

  // $('#diff_layout_snippet_0 .side_diff_result').mergely({
  //   cmsettings: { readOnly: true, lineNumbers: false, lineWrapping: true },
  //   editor_width: '200px',
  //   lhs: function(setValue) {
  //     setValue('hola como estas');
  //   },
  //   rhs: function(setValue) {
  //     setValue('adios como estas');
  //   }
  // });

});
