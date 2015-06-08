angular.module('madisonApp.controllers')
  .controller('ReaderController', ['$scope', '$http', 'annotationService', 'createLoginPopup', '$timeout', '$anchorScroll', function ($scope, $http, annotationService, createLoginPopup, $timeout, $anchorScroll) {
    var presentePlural = function(howMany) { return howMany == 1 ? '' : 'n'; };

    var howManySupport = function(howMany, doesSupport) {
      var verb = doesSupport ? ' apoya' : ' se opone';
      return howMany + verb + presentePlural(howMany);
    };

    $scope.annotations = [];
    $scope.$on('annotationsUpdated', function () {
      $scope.annotations = annotationService.annotations;
      $scope.$apply();

      $timeout(function () {
        $anchorScroll();
      }, 0);
    });

    $scope.init         = function () {
      $scope.user = user;
      $scope.doc  = doc;
      //$scope.setSponsor();
      $scope.getSupported();

      // Dates do not arrive in proper ISO 8601 format, e.g. 2015-01-14 03:27:04
      // But by adding the T we get timezone +00:00, same as in the HomeController
      // Then we parse it to get "seconds since epoch" which is needed by the date filter
      $scope.doc.created_at = Date.parse($scope.doc.created_at.replace(' ', 'T'));
      $scope.doc.updated_at = Date.parse($scope.doc.updated_at.replace(' ', 'T'));
    };
    $scope.setSponsor   = function () {
      try {
        if ($scope.doc.group_sponsor.length !== 0) {
          $scope.doc.sponsor  = $scope.doc.group_sponsor;
        } else {
          $scope.doc.sponsor  = $scope.doc.user_sponsor;
          $scope.doc.sponsor[0].display_name = $scope.doc.sponsor[0].fname + ' ' + $scope.doc.sponsor[0].lname;
        }
      } catch (err) {
        console.error(err);
      }
    };
    $scope.getSupported = function () {
      if ($scope.user.id !== '') {
      $http.get(_baseUrl + '/api/users/support/' + $scope.doc.id)
        .success(function (data) {
          switch (data.support) {
            case "1":
              $scope.supported    = true;
              break;
            case "":
              $scope.opposed      = true;
              break;
            default:
              $scope.supported    = null;
              $scope.opposed      = null;
          }

          if ($scope.supported !== null && $scope.opposed !== null) {
            $('#doc-support').text(howManySupport(data.supports, true));
            $('#doc-oppose').text(howManySupport(data.opposes, false));
          }
        }).error(function () {
          console.error("Unable to get support info for user %o and doc %o", $scope.user, $scope.doc);
        });
      }
    };
    $scope.support = function (supported, $event) {
      if ($scope.user.id === '') {
        createLoginPopup($event);
      } else {
        $http.post(_baseUrl + '/api/users/support/' + $scope.doc.id, {
          'support': supported
        })
        .success(function (data) {
          //Parse data to see what user's action is currently
          if (data.support === null) {
            $scope.supported    = false;
            $scope.opposed      = false;
          } else {
            $scope.supported    = data.support;
            $scope.opposed      = !data.support;
          }

          var button      = $($event.target);
          var otherButton = $($event.target).siblings('a.btn');

          if (button.hasClass('doc-support')) {
            button.text(howManySupport(data.supports, true));
            otherButton.text(howManySupport(data.opposes, false));
          } else {
            button.text(howManySupport(data.opposes, false));
            otherButton.text(howManySupport(data.supports, true));
          }
        })
        .error(function (data) {
          console.error("Error posting support: %o", data);
        });
      }

      $(document).ready(function () {
        var annotator;
        var popup;

        function attach_handlers() {
          $('html').on('click.popup', function () {
            $('.popup').remove();

            $('html').off('click.popup');
          });
        }

        function ajaxify_form(inForm, callback) {
          var form = $(inForm);
          form.submit(function (e) {
            e.preventDefault();

            $.post(form.attr('action'), form.serialize(), function (response) {

              if (response.errors && Object.keys(response.errors).length) {
                var error_html = $('<ul></ul>');

                /*jslint unparam: true*/
                angular.forEach(response.errors, function (value, key) {
                  error_html.append('<li>' + value + '</li>');
                });
                /*jslint unparam: false*/

                form.find('.errors').html(error_html);
              } else {
                callback(response);
              }
            });

          });
        }

        $('.affix-elm').each(function(i, elm) {
          elm = $(elm);
          var elmtop = 0;
          if(elm.data('offset-top')){
            elmtop = elm.data('offset-top');
          }
          var elmbottom = 0;
          if(elm.data('offset-bottom')){
            elmbottom = elm.data('offset-bottom');
          }

          elm.affix({
            offset: {
              top: elmtop,
              bottom: elmbottom
            }
          });
        });

        if (user.id === '') {

          Annotator.prototype.checkForEndSelection = function (event) {

            // This is what normally happens.
            var container, range, _k, _len2, _ref1;
            this.mouseIsDown = false;

            if (this.ignoreMouseup || $('.popup').length) {
              return;
            }
            this.selectedRanges = this.getSelectedRanges();
            _ref1 = this.selectedRanges;
            for (_k = 0, _len2 = _ref1.length; _k < _len2; _k++) {
              range = _ref1[_k];
              container = range.commonAncestor;
              if ($(container).hasClass("annotator-hl")) {
                container = $(container).parents("[class!=annotator-hl]")[0];
              }
              if (this.isAnnotator(container)) {
                return;
              }
            }
            if (event && this.selectedRanges.length) {
              // But we diverge from the norm here.

              if (event !== null) {
                event.preventDefault();
              }

              popup = $('<div class="popup unauthed-popup"><p>Ingresa para comentar.</p>' +
                '<input type="button" id="login" value="Ingresar" class="btn btn-primary"/>' +
                '<input type="button" id="signup" value="Registrarse" class="btn btn-primary" /></div>');

              popup.on('click.popup', function (event) {
                event.stopPropagation();
              });

              $('#login', popup).click(function (event) {
                event.stopPropagation();
                event.preventDefault();

                $.get(_baseUrl + '/api/user/login', {}, function (data) {
                  data = $(data);

                  ajaxify_form(data.find('form'), function () {
                    $('html').trigger('click.popup');

                    location.reload(false);
                  });

                  popup.html(data);
                });
              });

              $('#signup', popup).click(function (event) {
                event.stopPropagation();
                event.preventDefault();

                $.get(_baseUrl + '/api/user/signup', {}, function (data) {
                  data = $(data);

                  ajaxify_form(data.find('form'), function (result) {
                    $('html').trigger('click.popup');
                    alert(result.message);
                  });

                  popup.html(data);
                });
              });

              $('body').append(popup);

              var position = {
                'top': event.pageY - popup.height(),
                'left': event.clientX
              };
              popup.css(position).css('position', 'absolute');

              this.ignoreMouseup = false;
              setTimeout(attach_handlers, 50);
            }

          };
        }

        annotator = $('#doc_content').annotator({
          //readOnly: user.id == ''
        });

        annotator.annotator('addPlugin', 'Unsupported');
        annotator.annotator('addPlugin', 'Tags');
        annotator.annotator('addPlugin', 'Markdown');
        annotator.annotator('addPlugin', 'Store', {
          annotationData: {
            'uri': window.location.pathname,
            'comments': []
          },
          prefix: _baseUrl + '/api/docs/' + doc.id + '/annotations',
          urls: {
            create: '',
            read: '/:id',
            update: '/:id',
            destroy: '/:id',
            search: '/search'
          }
        });

        annotator.annotator('addPlugin', 'Permissions', {
          user: user,
          permissions: {
            'read': [],
            'update': [user.id],
            'delete': [user.id],
            'admin': [user.id]
          },
          showViewPermissionsCheckbox: false,
          showEditPermissionsCheckbox: false,
          userId: function (user) {
            if (user && user.id) {
              return user.id;
            }

            return user;
          },
          userString: function (user) {
            if (user && user.name) {
              return user.name;
            }

            return user;
          }
        });

        annotator.annotator('addPlugin', 'Madison', {
          userId: user.id
        });
      });
    };
  }]);
