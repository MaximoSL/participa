angular.module('madisonApp.controllers')
  .controller('HomePageController', ['$scope', '$location', '$http', '$filter', '$cookies', 'Doc', function ($scope, $location, $http, $filter, $cookies, Doc) {
    var refEl     = $('.main-banner'),
        search    = $location.search(),
        page      = (search.page) ? search.page : 1,
        limit     = (search.limit) ? search.limit : 20,
        docSearch = (search.q) ? search.q : '';
        docFilter = (search.mode) ? search.mode : '';
        docOrder  = (search.date) ? search.date : '';

    var fetchDocs = function() {
      $scope.docs     = Array();
      $scope.updating = true;

      var params = {
        q: docSearch,
        filter: docFilter,
        order: docOrder,
        page: page,
        per_page: limit
      };

      params = _.pick(params, function(value, key, object) {
        return value !== '';
      });

      Doc.query(params, function (data) {
        $scope.totalDocs = data.pagination.count;
        $scope.perPage   = data.pagination.per_page;
        $scope.page      = data.pagination.page;
        $scope.updating  = false;
        $scope.docs      = data.results;
      }).$promise.catch(function (data) {
        console.error("Unable to get documents: %o", data);
      });
    };

    $(function() {
      $('#home-select2-filter').select2({
        placeholder: "Categoría, autor o estatus",
        allowClear: true
      });
      $('#home-select2-order').select2({
        placeholder: "Fecha",
        allowClear: true
      });

      $('.select2-focusser').each(function(){
        $(this).attr('aria-label', $(this).attr('id'));
      });
    });

    $scope.docs      = [];
    $scope.reverse   = true;
    $scope.startStep = 0;
    $scope.updating  = false;
    $scope.docSearch = docSearch;
    $scope.docFilter = docFilter;
    $scope.docOrder  = docOrder;

    $scope.paginate = function () {
      if ($scope.page > 1) {
        $location.search("page", $scope.page);
      } else {
        $location.search("page", null);
      }

      page = $scope.page;

      // Scroll to the top of the list
      $('html, body').animate({
        scrollTop : refEl.offset().top + refEl.height()
      }, 500 );

      fetchDocs();
    };

    $scope.search = function () {
      if ($scope.docSearch) {
        $location.search("q", $scope.docSearch);
      } else {
        $location.search("q", null);
      }

      if ($scope.docFilter) {
        $location.search("filter", $scope.docFilter);
      } else {
        $location.search("filter", null);
      }

      if ($scope.docOrder) {
        $location.search("order", $scope.docOrder);
      } else {
        $location.search("order", null);
      }

      docSearch = $scope.docSearch;
      docFilter = $scope.docFilter;
      docOrder = $scope.docOrder;
      fetchDocs();
    };

    // $scope.parseDocs = function (docs) {
    //     angular.forEach(docs, function (doc) {
    //         $scope.docs.unshift(doc);
    //
    //         angular.forEach(doc.dates, function (date) {
    //             date.date = Date.parse(date.date);
    //         });
    //     });
    // };

    fetchDocs();
  }]);
