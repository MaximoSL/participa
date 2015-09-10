$(document).ready(function () {

  var dmp = new diff_match_patch();
  dmp.Diff_Timeout = 1;
  dmp.Diff_EditCost = 5;

  function getInlineDiff() {
    $('.show_diff_inline').each(function(){
      $element = $(this);

      var text1 = $element.find('.text1').html();
      var text2 = $element.find('.text2').html();
      var d = dmp.diff_main(text1, text2);

      dmp.diff_cleanupEfficiency(d);

      var ds = dmp.diff_prettyHtml(d);
      ds = ds.replace(/<span>&para;/g, '<span>');
      ds = ds.replace('<br>', '');

      // $element.find('.diff_result').html(ds + '<BR>Time: ' + (ms_end - ms_start) / 1000 + 's');
      $element.find('.diff_result').html(ds);
      $element.find('.text1').hide();
      $element.find('.text2').hide();
    });
  }

  getInlineDiff();

});
