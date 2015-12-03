/*global Annotator*/
/*global getAnnotationService*/
/*global user*/
/*global doc*/
/*global diff_match_patch*/
Annotator.Plugin.Madison = function () {
  Annotator.Plugin.apply(this, arguments);
};

$.extend(Annotator.Plugin.Madison.prototype, new Annotator.Plugin(), {
  events: {},
  options: {},
  pluginInit: function () {

    /**
     *  Subscribe to Store's `annotationsLoaded` event
     *    Stores all annotation objects provided by Store in the window
     *    Adds all annotations to the sidebar
     **/
    this.annotator.subscribe('annotationsLoaded', function (annotations) {
      annotations.forEach(function (annotation) {
        annotation.highlights.forEach(function (highlight) {
          $(highlight).attr('id', 'annotation_' + annotation.id);
          $(highlight).attr('name', 'annotation_' + annotation.id);
          annotation.link = 'annotation_' + annotation.id;
        });
      });

      //Set the annotations in the annotationService
      var annotationService = getAnnotationService();
      annotationService.setAnnotations(annotations);
    });

    /**
     *  Subscribe to Annotator's `annotationCreated` event
     *    Adds new annotation to the sidebar
     */
    this.annotator.subscribe('annotationCreated', function (annotation) {
      var annotationService = getAnnotationService();
      annotationService.addAnnotation(annotation);
      if ($.showAnnotationThanks) {
        $('#annotationThanks').modal({
          remote: _baseUrl + '/modals/annotation_thanks',
          keyboard: true
        });
      }
    });

    this.annotator.subscribe('commentCreated', function (comment) {
      comment = $('<div class="existing-comment"><blockquote>' + comment.text + '<div class="comment-author">' + comment.user.name + '</div></blockquote></div>');
      var currentComments = $('#current-comments');
      currentComments.append(comment);
      currentComments.removeClass('hidden');

      $('#current-comments').collapse(true);
    });

    this.annotator.subscribe('annotationViewerTextField', function (field, annotation) {
      if(annotation.tags.length === 0){
        return;
      }

      var showDiff = false;

      annotation.tags.forEach(function (tag){
        if(tag === 'editar'){
          var jField = $(field);
          var differ = new diff_match_patch();
          var diffs = differ.diff_main(annotation.quote, annotation.text);
          var html = differ.diff_prettyHtml(diffs);
          jField.find('p').html(html);
        }
      });
    });

    //Add Madison-specific fields to the viewer when Annotator loads it
    this.annotator.viewer.addField({
      load: function (field, annotation) {
        this.addNoteLink(field, annotation);
        this.addNoteActions(field, annotation);
        this.addComments(field, annotation);
      }.bind(this)
    });

    this.annotator.editor.submit = function (e) {
      //Clear previous errors
      this.annotation._error = false;

      var field, _i, _len, _ref;
      Annotator.Util.preventEventDefault(e);

      _ref = this.fields;

      for (_i = 0, _len = _ref.length; _i < _len; _i++){
        field = _ref[_i];
        field.submit(field.element, this.annotation);
      }

      if(this.annotation._error !== true){
        this.publish('save', [this.annotation]);

        return this.hide();
      }
    };

    errorNotification = function (message) {
      Annotator.showNotification(message.replace(/<\/?[^>]+(>|$)/g, ""),Annotator.Notification.ERROR);
      feedbackMessage( message, 'error', '#participate-activity-message' );
    }

    this.annotator.editor.addField({
      load: function (field, annotation) {
        this.addEditFields(field, annotation);
      }.bind(this),
      submit: function(field, annotation) {
        //check it is tagged 'edit'
        if(this.hasEditTag(annotation.tags)){
          //check we have explanatory content
          var explanation = $(field).find('#explanation').val();

          //If no explanatory content, show message and don't submit
          if('' == explanation.trim()){
            $('#annotation-error').text("Por favor explica por qué hiciste el cambio.").toggle(true);

            annotation._error = true;
            return false;
          }

          annotation.explanation = explanation;
        }
      },
      hasEditTag: function (tags) {
        var hasEditTag = false;

        if(tags === undefined || tags.length  === 0){
          return false;
        }

        tags.forEach(function (tag) {
          if (tag === 'editar') {
            hasEditTag = true;
          }
        });

        return hasEditTag;
      }
    });
  },
  addEditFields: function (field, annotation) {
    var newField = $(field);
    var toAdd = $('<div class="annotator-editor-edit-wrapper"></div>');

    var buttonGroup = $('<div class="btn-group"></div>');

    var explanation = $('<input id="explanation" type="text" name="explanation" placeholder="¿Por qué editaste esto?" style="display:none;" />');
    var annotationError = $('<p id="annotation-error" style="display:none; color:red;"></p>');

    var annotateButton = $('<button type="button" class="btn btn-default active">Anotar</button>').click(function () {
      $(this).addClass('active');
      $(this).siblings().each(function (sibling) {
        $(this).removeClass('active');
      });
      $('#annotator-field-0').val('');
      $('#annotator-field-1').val('');
      $('#explanation').toggle(false);
      $('#explanation').prop('required', false);
      $('#annotator-error').text('').toggle(false);
      $('#annotator-field-0').focus();
    });

    var editButton = $('<button type="button" class="btn btn-default">Editar</button>').click(function () {
      $(this).addClass('active');
      $(this).siblings().each(function (sibling) {
        $(this).removeClass('active');
      });
      $('#annotator-field-0').val(annotation.quote);
      $('#annotator-field-1').val('editar');
      $('#explanation').toggle(true);
      $('#explanation').prop('required', true);
      $('#annotator-field-0').focus();
    });

    buttonGroup.append(annotateButton, editButton);
    toAdd.append(buttonGroup);
    toAdd.append(explanation);
    toAdd.append(annotationError);
    newField.html(toAdd);
  },
  addComments: function (field, annotation) {
    //Add comment wrapper and collapse the comment thread
    var commentsHeader = $('<div class="comment-toggle" data-toggle-"collapse" data-target="#current-comments">Comentarios <span id="comment-caret" class="caret caret-right"></span></button>').click(function () {
      $('#current-comments').collapse('toggle');
      $('#comment-caret').toggleClass('caret-right');
    });

    //If there are no comments, hide the comment wrapper
    if ($(annotation.comments).length === 0) {
      commentsHeader.addClass('hidden');
    }

    //Add all current comments to the annotation viewer
    var currentComments = $('<div id="current-comments" class="current-comments collapse"></div>');

    /*jslint unparam: true*/
    $.each(annotation.comments, function (index, comment) {
      comment = $('<div class="existing-comment"><blockquote>' + comment.text + '<div class="comment-author">' + comment.user.name + '</div></blockquote></div>');
      currentComments.append(comment);
    });
    /*jslint unparam: false*/

    //Collapse the comment thread on load
    currentComments.ready(function () {
      $('#existing-comments').collapse({
        toggle: false
      });
    });

    //If the user is logged in, allow them to comment
    if (user.id !== '' && doc.is_opened) {
      var annotationComments = $('<div class="annotation-comments"></div>');
      var commentText = $('<input type="text" class="form-control" />');
      var commentSubmit = $('<button type="button" class="btn btn-primary" >Enviar</button>');
      commentSubmit.click(function () {
        this.createComment(commentText, annotation);
      }.bind(this));
      annotationComments.append(commentText);

      annotationComments.append(commentSubmit);

      $(field).append(annotationComments);
    }

    $(field).append(commentsHeader, currentComments);
  },
  addNoteActions: function (field, annotation) {
    //Add actions ( like / dislike / error) to annotation viewer
    var annotationAction = $('<div></div>').addClass('annotation-action');
    var generalAction = $('<span></span>').addClass('glyphicon').data('annotation-id', annotation.id);

    var annotationLike = generalAction.clone().addClass('glyphicon-thumbs-up').append('<span class="action-count">' + annotation.likes + '</span>');
    var annotationDislike = generalAction.clone().addClass('glyphicon-thumbs-down').append('<span class="action-count">' + annotation.dislikes + '</span>');
    var annotationFlag = generalAction.clone().addClass('glyphicon-flag').append('<span class="action-count">' + annotation.flags + '</span>');

    annotationAction.append(annotationLike, annotationDislike, annotationFlag);

    //If user is logged in add his current action and enable the action buttons
    if (user.id !== '') {
      if (annotation.user_action) {
        if (annotation.user_action === 'like') {
          annotationLike.addClass('selected');
        } else if (annotation.user_action === 'dislike') {
          annotationDislike.addClass('selected');
        } else if (annotation.user_action === 'flag') {
          annotationFlag.addClass('selected');
        } // else this user doesn't have any actions on this annotation
      }

      var that = this;

      annotationLike.addClass('logged-in').click(function () {
        that.addLike(annotation, this);
      });

      annotationDislike.addClass('logged-in').click(function () {
        that.addDislike(annotation, this);
      });

      annotationFlag.addClass('logged-in').click(function () {
        that.addFlag(annotation, this);
      });
    }

    $(field).append(annotationAction);
  },
  addNoteLink: function (field, annotation) {
    //Add link to annotation
    var noteLink = $('<div class="annotation-link"></div>');
    var linkPath = _currentPath + '#' + annotation.link;
    var annotationLink = $('<a></a>').attr('href', window.location.pathname + '#' + annotation.link).text('Copiar Enlace de Anotación').addClass('annotation-permalink');
    annotationLink.attr('data-clipboard-text', linkPath);

    var client = new ZeroClipboard(annotationLink);

    noteLink.append(annotationLink);
    $(field).append(noteLink);
  },
  createComment: function (textElement, annotation) {
    var text = textElement.val();
    textElement.val('');

    var comment = {
      text: text,
      user: user
    };

    //POST request to add user's comment
    $.post(_baseUrl + '/api/docs/' + doc.id + '/annotations/' + annotation.id + '/comments', {
      comment: comment
    }, function (data) {

      if(data.status === 'error') {
        message = '<b>Lo sentimos</b>, Este documento se encuentra cerrado';
        return errorNotification(message);
      }

      annotation.comments.push(comment);
      udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + doc.slug + '.annotation.' + annotation.id + '&action=comment&comment_text=' + comment.text );

      return this.annotator.publish('commentCreated', comment);
    }.bind(this));
  },
  addLike: function (annotation, element) {
    $.post(_baseUrl + '/api/docs/' + doc.id + '/annotations/' + annotation.id + '/likes', function (data) {
      udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + doc.slug + '.annotation_vote.' + annotation.id + '&action=like' );
      element = $(element);
      element.children('.action-count').text(data.likes);
      element.siblings('.glyphicon').removeClass('selected');

      if(typeof data.document_closed !== 'undefined'){
        message = '<b>Lo sentimos</b>, Este documento se encuentra cerrado';
        return errorNotification(message);
      }

      if (data.action) {
        element.addClass('selected');
      } else {
        element.removeClass('selected');
      }

      element.siblings('.glyphicon-thumbs-up').children('.action-count').text(data.likes);
      element.siblings('.glyphicon-thumbs-down').children('.action-count').text(data.dislikes);
      element.siblings('.glyphicon-flag').children('.action-count').text(data.flags);

      annotation.likes = data.likes;
      annotation.dislikes = data.dislikes;
      annotation.flags = data.flags;
      annotation.user_action = 'like';
    });
  },
  addDislike: function (annotation, element) {
    $.post(_baseUrl + '/api/docs/' + doc.id + '/annotations/' + annotation.id + '/dislikes', function (data) {
      udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + doc.slug + '.annotation_vote.' + annotation.id + '&action=dislike' );
      element = $(element);
      element.children('.action-count').text(data.dislikes);
      element.siblings('.glyphicon').removeClass('selected');

      if(typeof data.document_closed !== 'undefined'){
        message = '<b>Lo sentimos</b>, Este documento se encuentra cerrado';
        return errorNotification(message);
      }

      if (data.action) {
        element.addClass('selected');
      } else {
        element.removeClass('selected');
      }

      element.siblings('.glyphicon-thumbs-up').children('.action-count').text(data.likes);
      element.siblings('.glyphicon-thumbs-down').children('.action-count').text(data.dislikes);
      element.siblings('.glyphicon-flag').children('.action-count').text(data.flags);

      annotation.likes = data.likes;
      annotation.dislikes = data.dislikes;
      annotation.flags = data.flags;
      annotation.user_action = 'dislike';
    });
  },
  addFlag: function (annotation, element) {
    $.post(_baseUrl + '/api/docs/' + doc.id + '/annotations/' + annotation.id + '/flags', function (data) {
      udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + doc.slug + '.annotation_vote.' + annotation.id + '&action=flag' );
      element = $(element);
      element.children('.action-count').text(data.flags);
      element.siblings('.glyphicon').removeClass('selected');

      if(typeof data.document_closed !== 'undefined'){
        message = '<b>Lo sentimos</b>, Este documento se encuentra cerrado';
        return errorNotification(message);
      }

      if (data.action) {
        element.addClass('selected');
      } else {
        element.removeClass('selected');
      }

      element.siblings('.glyphicon-thumbs-up').children('.action-count').text(data.likes);
      element.siblings('.glyphicon-thumbs-down ').children('.action-count').text(data.dislikes);

      annotation.likes = data.likes;
      annotation.dislikes = data.dislikes;
      annotation.flags = data.flags;
      annotation.user_action = 'flag';
    });
  }
});

angular.module( 'madisonApp.controllers', []);
angular.module( 'madisonApp.controllers' )
    .controller( 'AnnotationController', [ '$scope', '$sce', '$http', 'annotationService', 'createLoginPopup', 'growl', '$location', '$filter', '$timeout', function ( $scope, $sce, $http, annotationService, createLoginPopup, growl, $location, $filter, $timeout ) {
        $scope.annotations  = [];
        $scope.supported    = null;
        $scope.opposed      = false;

        //Parse sub-comment hash if there is one
        var hash            = $location.hash();
        var subCommentId    = hash.match( /^annsubcomment_([0-9]+)$/ );
        if ( subCommentId ) {
            $scope.subCommentId = subCommentId[1];
        }

        //Watch for annotationsUpdated broadcast
        $scope.$on('annotationsUpdated', function () {
            angular.forEach( annotationService.annotations, function ( annotation ) {
                if ( $.inArray( annotation, $scope.annotations ) < 0 ) {
                    var collapsed = true;
                    if ( $scope.subCommentId ) {
                        angular.forEach( annotation.comments, function ( subcomment ) {
                            if ( subcomment.id == $scope.subCommentId ) {
                                collapsed = false;
                            }
                        });
                    }

                    annotation.label                = 'annotation';
                    annotation.commentsCollapsed    = collapsed;
                    $scope.annotations.push( annotation );
                }
            });

            $scope.$apply();
        });

        $scope.init             = function ( docId ) {
            $scope.user = user;
            $scope.doc  = doc;
        };
        $scope.isSponsor        = function () {
            var currentId   = $scope.user.id;
            var sponsored   = false;

            angular.forEach( $scope.doc.sponsor, function ( sponsor ) {
                if ( currentId === sponsor.id ) {
                    sponsored = true;
                }
            });

            return sponsored;
        };
        $scope.notifyAuthor     = function ( annotation ) {
            $http.post( _baseUrl + '/api/docs/' + doc.id + '/annotations/' + annotation.id + '/' + 'seen' )
                .success(function ( data ) {
                    annotation.seen = data.seen;
                }).error(function ( data ) {
                    console.error( "Unable to mark activity as seen: %o", data );
                });
        };
        $scope.getDocComments   = function ( docId ) {
            $http({
                method  : 'GET',
                url     : _baseUrl + '/api/docs/' + docId + '/comments'
            })
            .success( function ( data ) {
                angular.forEach( data, function ( comment ) {
                    var collapsed = false;
                    if ( $scope.subCommentId ) {
                        angular.forEach( comment.comments, function ( subcomment ) {
                            if ( subcomment.id == $scope.subCommentId ) {
                                collapsed = false;
                            }
                        });
                    }

                    comment.commentsCollapsed   = collapsed;
                    comment.label               = 'comment';
                    comment.link                = 'comment_' + comment.id;
                    $scope.annotations.push( comment );
                });
            })
            .error( function ( data ) {
                console.error( "Error loading comments: %o", data );
            });
        };
        $scope.commentSubmit    = function () {
            var comment     = angular.copy( $scope.comment );
            comment.user    = $scope.user;
            comment.doc     = $scope.doc;

            $http.post( _baseUrl + '/api/docs/' + comment.doc.id + '/comments', {
                'comment'   : comment
                })
                .success( function () {
                    comment.label       = 'comment';
                    comment.user.fname  = comment.user.name;
                    $scope.stream.push( comment );
                    $scope.comment.text = '';

                    feedbackMessage( '<b>¡Gracias!</b> Acabas de agregar un comentario', 'success', '#participate-activity-message' );
                })
                .error( function ( data ) {
                    console.error( "Error posting comment: %o", data );
                });
        };
        $scope.activityOrder    = function ( activity ) {
            var popularity  = activity.likes - activity.dislikes;

            return popularity;
        };
        $scope.addAction        = function ( activity, action, $event ) {
            if ( $scope.user.id !== '' ) {
                $http.post( _baseUrl + '/api/docs/' + $scope.doc.id + '/' + activity.label + 's/' + activity.id + '/' + action )
                    .success( function ( data ) {
                        activity.likes      = data.likes;
                        activity.dislikes   = data.dislikes;
                        activity.flags      = data.flags;

                        udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + $scope.doc.slug + '.annotation.' + activity.id + '&action=' + action );

                        if(typeof data.document_closed !== 'undefined'){
                          growl.error('Éste documento se encuentra cerrado');
                        }
                    }).error( function ( data ) {
                        console.error( data );
                    });
            } else {
              createLoginPopup($event);
            }
        };
        $scope.collapseComments = function ( activity ) {
            activity.commentsCollapsed  = !activity.commentsCollapsed;
        };
        $scope.subcommentSubmit = function ( activity, subcomment ) {
            if ( $scope.user.id === '' ) {
                var focused = document.activeElement;

                if ( document.activeElement == document.body ) {
                    pageY   = $( window ).scrollTop() + 300;
                    clientX = $( window ).width() / 2 - 100;
                } else {
                    pageY   = $( focused ).offset().top;
                    clientX = $( focused ).offset().left;
                }

                createLoginPopup( jQuery.Event( "click", {
                    clientX : clientX,
                    pageY   : pageY
                }));
                return;
            }

            subcomment.user = $scope.user;

            $.post( _baseUrl + '/api/docs/' + $scope.doc.id + '/' + activity.label + 's/' + activity.id + '/comments', {
                    'comment'   : subcomment
                })
                .success( function ( data ) {

                    if(data.status === 'error') {
                      growl.error('Éste documento se encuentra cerrado');
                      return;
                    }

                    activity.comments.push( data );
                    subcomment.text = '';
                    subcomment.user = '';
                    $scope.$apply();

                    feedbackMessage( '<b>¡Gracias!</b> Acabas de agregar un comentario', 'success', '#participate-activity-message' );
                }).error( function ( data ) {
                    console.error( data );
                });
        };
    }]);

angular.module('madisonApp.controllers')
  .controller('AppController', ['$rootScope', '$scope', 'UserService', function ($rootScope, $scope, UserService) {
    // Update page title
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
      $rootScope.pageTitle = current.$$route.title;
    });

    // Watch for user data change
    $scope.$on('userUpdated', function () {
      $scope.user = UserService.user;
    });

    // Load user data
    UserService.getUser();
  }]);

angular.module( 'madisonApp.controllers' )
    .controller( 'CommentController', [ '$scope', '$sce', '$http', 'annotationService', 'createLoginPopup', 'growl', '$location', '$filter', '$timeout', function ( $scope, $sce, $http, annotationService, createLoginPopup, growl, $location, $filter, $timeout ) {
        $scope.comments             = [];
        $scope.supported            = null;
        $scope.opposed              = false;
        $scope.collapsed_comment    = {};

      // Parse comment/subcomment direct links
        var hash            = $location.hash();
        var subCommentId    = hash.match( /(sub)?comment_([0-9]+)$/ );
        if ( subCommentId ) {
            $scope.subCommentId = subCommentId[2];
        }

        $scope.init             = function ( docId, disableAuthor, disableCommentAction ) {
            $scope.getDocComments( docId );
            $scope.user                 = user;
            $scope.doc                  = doc;
            $scope.disableAuthor        = ( typeof disableAuthor !== 'undefined' && disableAuthor == true );
            $scope.disableCommentAction = ( typeof disableCommentAction !== 'undefined' );
            $scope.getLayoutTexts();
        };
        $scope.isSponsor        = function ( userId ) {
            var currentId   = userId || $scope.user.id;
            var sponsored   = false;

            angular.forEach( $scope.doc.sponsor, function ( sponsor ) {
                if ( currentId === sponsor.id ) {
                    sponsored = true;
                }
            });

            return sponsored;
        };
        $scope.notifyAuthor     = function ( activity ) {
            // If the current user is a sponsor and the activity hasn't been seen yet,
            // post to API route depending on comment/annotation label
            $http.post(_baseUrl + '/api/docs/' + doc.id + '/' + 'comments/' + activity.id + '/' + 'seen' )
                .success( function ( data ) {
                    activity.seen = data.seen;
                }).error( function ( data ) {
                    console.error( "Unable to mark activity as seen: %o", data );
                });
        };
        $scope.getLayoutTexts   = function() {
            var texts = {
                    common  : {
                        header                      : '',
                        callToAction                : '',
                        commentLabel                : 'Agrega un comentario:',
                        commentPlaceholder          : 'Agregar un comentario',
                        subCommentPlaceholder       : 'Agregar un comentario',
                        commentfeedbackMessage      : '<b>¡Gracias!</b> Acabas de agregar un comentario',
                        subCommentfeedbackMessage   : '<b>¡Gracias!</b> Acabas de agregar un comentario',
                        privateComment              : 'Comentario privado',
                        sendComment                 : 'Enviar'
                    },
                    ieda    : {
                        header                      : 'Categorías de Datos Abiertos propuestos',
                        callToAction                : 'Vota por los conjuntos de datos que te interesan',
                        commentLabel                : 'Sugiere otra categoría:',
                        commentPlaceholder          : 'Sugiere otra categoría',
                        subCommentPlaceholder       : 'Sugiere otro conjunto',
                        commentfeedbackMessage      : '<b>¡Gracias!</b> Acabas de sugerir una categoría',
                        subCommentfeedbackMessage   : '<b>¡Gracias!</b> Acabas de sugerir un conjunto de datos',
                        privateComment              : 'Categoría privada',
                        sendComment                 : 'Enviar'
                    },
                    planAGA : {
                        header                      : 'Temas para el Tercer Plan de Acción de la Alianza para el Gobierno Abierto',
                        callToAction                : 'Vota y comenta los temas que más te interesan.',
                        commentLabel                : 'Sugiere otro tema:',
                        commentPlaceholder          : 'Sugiere otro tema',
                        subCommentPlaceholder       : 'Sugiere otro subtema',
                        commentfeedbackMessage      : '<b>¡Gracias!</b> Acabas de sugerir un tema',
                        subCommentfeedbackMessage   : '<b>¡Gracias!</b> Acabas de sugerir un subtema',
                        privateComment              : 'Tema privado',
                        sendComment                 : 'Enviar'
                    },
                    cofemer : {
                        header                      : '',
                        callToAction                : '',
                        commentLabel                : 'Agrega tu comentario:',
                        commentPlaceholder          : 'Agrega tu comentario',
                        subCommentPlaceholder       : 'Agrega tu comentario',
                        commentfeedbackMessage      : '<b>¡Gracias!</b> Acabas de agregar un comentario',
                        subCommentfeedbackMessage   : '<b>¡Gracias!</b> Acabas de agregar un comentario',
                        privateComment              : 'Comentario privado',
                        sendComment                 : 'Enviar'
                    }
                };

            $scope.layoutTexts  = texts.common;
            angular.forEach( $scope.doc.doc_layouts, function ( category ) {
                if ( texts[category.name] !== undefined )
                    $scope.layoutTexts  = texts[category.name];
            });
        };
        $scope.getDocComments   = function ( docId ) {
            // Get all doc comments, regardless of nesting level
            $http({
                method  : 'GET',
                url     : _baseUrl + '/api/docs/' + docId + '/comments'
            })
                .success( function ( data ) {
                    // Build child-parent relationships for each comment
                    angular.forEach( data, function ( comment ) {
                        // If this isn't a parent comment, we need to find the parent and push this comment there
                        if ( comment.parent_id !== null ) {
                            var parent  = $scope.parentSearch( data, comment.parent_id );
                            comment.parentpointer   = data[parent];
                            data[parent].comments.push( comment );
                        }

                        // If this is the comment being linked to, save it
                        if ( comment.id == $scope.subCommentId ) {
                            $scope.collapsed_comment = comment;
                        }

                        comment.commentsCollapsed   = true;
                        comment.label               = 'comment';
                        comment.link                = 'comment_' + comment.id;

                        // We only want to push top-level comments, they will include subcomments in their comments array(s)
                        if ( comment.parent_id === null ) {
                            $scope.comments.push( comment );
                        }
                    });

                    // If we are linking directly to a comment, we need to expand comments
                    if ( $scope.subCommentId ) {
                        var not_parent = true;
                        // Expand comments, moving up towards the parent, until all are expanded
                        do {
                            $scope.collapsed_comment.commentsCollapsed  = false;
                            if ( $scope.collapsed_comment.parent_id !== null ) {
                                $scope.collapsed_comment    = $scope.collapsed_comment.parentpointer;
                            } else {
                                // We have reached the first sublevel of comments, so set the top level
                                // parent to expand and exit
                                not_parent  = false;
                            }
                        } while ( not_parent === true );
                    }
                }).error( function ( data ) {
                    console.error( "Error loading comments: %o", data );
                });
        };
        $scope.parentSearch     = function ( arr, val ) {
            for ( var i = 0; i < arr.length; i++ )
                if ( arr[i].id === val )
                    return i;
                return false;
        };
        $scope.commentSubmit    = function () {
            // Add comscore analytics
            udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + $scope.doc.slug + '&comment_text=' + $scope.comment.text );

            var comment     = angular.copy( $scope.comment );
            comment.user    = $scope.user;
            comment.doc     = $scope.doc;

            $http.post(_baseUrl + '/api/docs/' + comment.doc.id + '/comments', {
                'comment': comment
            })
                .success( function ( data ) {

                    data[0].label   = 'comment';
                    $scope.comments.push( data[0] );
                    $scope.comment.text = '';

                    if(typeof data.document_closed !== 'undefined'){
                      growl.error('Éste documento se encuentra cerrado');
                      return;
                    }

                    feedbackMessage( $scope.layoutTexts.commentfeedbackMessage, 'success', '#participate-comment-message' );
                })
                .error( function ( data ) {
                    console.error( "Error posting comment: %o", data );
                });
        };
        $scope.activityOrder    = function ( activity ) {
            var popularity  = activity.likes - activity.dislikes;

            return popularity;
        };
        $scope.addAction        = function ( activity, action, $event ) {
            if ( $scope.user.id !== '' ) {
                if ( action == 'likes' || action == 'dislikes' ) {
                    // Add comscore analytics
                    var vote  = ( action == 'likes' ) ? 'up_vote' : 'down_vote';
                }

                $http.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/' + activity.label + 's/' + activity.id + '/' + action )
                    .success( function ( data ) {
                        udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + $scope.doc.slug + '.comment.' + activity.id + '&action=' + action );
                        activity.likes  = data.likes;
                        activity.dislikes   = data.dislikes;
                        activity.flags      = data.flags;
                        activity.deleted_at = data.deleted_at;

                        if(typeof data.document_closed !== 'undefined'){
                          growl.error('Éste documento se encuentra cerrado');
                        }
                    }).error( function ( data ) {
                        console.error( data );
                    });
            } else {
              createLoginPopup($event);
            }
        };
        $scope.collapseComments = function ( activity ) {
            activity.commentsCollapsed = !activity.commentsCollapsed;
        };
        $scope.subcommentSubmit = function ( activity, subcomment ) {
            // Add comscore analytics
            udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + $scope.doc.slug + '&subcomment_text=' + subcomment.text + '&subcomment_parent_id=' + subcomment.parent_id );

            if ( $scope.user.id === '' ) {
                var focused = document.activeElement;

                if ( document.activeElement == document.body ) {
                    pageY   = $( window ).scrollTop() + 300;
                    clientX = $( window ).width() / 2 - 100;
                } else {
                    pageY   = $( focused ).offset().top;
                    clientX = $( focused ).offset().left;
                }

                createLoginPopup( jQuery.Event( "click", {
                    clientX : clientX,
                    pageY   : pageY
                }));
                return;
            }

            subcomment.user = $scope.user;

            $.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/' + activity.label + 's/' + activity.id + '/comments', {
                'comment': subcomment
            })
                .success( function ( data ) {
                    data.comments   = [];
                    data.label      = 'comment';
                    activity.comments.push( data );
                    subcomment.text = '';
                    subcomment.user = '';
                    $scope.$apply();

                    feedbackMessage( $scope.layoutTexts.subCommentfeedbackMessage, 'success', '#participate-comment-message' );
            }).error( function ( data ) {
                console.error( data );
            });
        };
    }]);

angular.module('madisonApp.controllers')
  .controller('DocumentPageController', ['$scope', '$cookies', '$location', 'Doc', '$sce', function ($scope, $cookies, $location, Doc, $sce) {
    $scope.hideIntro = $cookies.hideIntro;

    // Check which tab needs to be active - if the location hash
    // is #annsubcomment or there is no hash, the annotation/bill tab needs to be active
    // Otherwise, the hash is #subcomment/#comment and the discussion tab should be active
    var annotationHash = $location.hash().match(/^annsubcomment_([0-9]+)$/);
    $scope.secondtab = false;
    $scope.inlinediff = false;

    if (!annotationHash && ($location.hash())) {
      $scope.secondtab = true;
    }

    $scope.hideHowToAnnotate    = function () {
      $cookies.hideIntro = true;
      $scope.hideIntro   = true;
    };

    $scope.doc  = Doc.get({
      id  : doc.id
    }, function () {
      //If intro text exists, convert & trust the markdown content
      if(undefined !== $scope.doc.introtext[0]){
          var converter    = new Markdown.Converter();
          $scope.introtext = $sce.trustAsHtml(converter.makeHtml($scope.doc.introtext[0].meta_value));
      }
    });
  }]);

angular.module( 'madisonApp.controllers' )
    .controller( 'DocumentTocController', [ '$scope', function ( $scope ) {
        $scope.headings = [];

        // For now, we use the simplest possible method to render the TOC -
        // just scraping the content.  We could use a real API callback here
        // later if need be.  A huge stack of jQuery follows.
        var headings    = $( '#doc_content' ).find( 'h1,h2,h3,h4,h5,h6' );

        if( headings.length > 0 ) {
            headings.each( function( i, elm ) {
                elm = $( elm );
                // Set an arbitrary id.
                // TODO: use a better identifier here - preferably a title-based slug
                if( !elm.attr( 'id' ) ) {
                    elm.attr( 'id', 'heading-' + i );
                }
                elm.addClass( 'anchor' );
                $scope.headings.push( {
                    'title' : elm.text(),
                    'tag'   : elm.prop( 'tagName' ),
                    'link'  : elm.attr( 'id' )
                });
            });
        } else {
            $( '#toc-column' ).remove();
            var container   = $( '#content' ).parent();
            container.removeClass( 'col-md-6' );
            container.addClass( 'col-md-9' );
        }
    }]);

angular.module('madisonApp.controllers')
  .controller('EmailSubscribeController', ['$scope', '$http', function ($scope, $http) {
    $scope.email = '';
    $scope.successMessage = false;
    $scope.subscribeEmail = function () {
      $http.post('http://www.gob.mx/subscribe', { email: $scope.email })
        .success(function (data) {
          $scope.successMessage = true;
        }).error(function (data) {
          console.error( "Unable to mark activity as seen: %o", data );
        });
    };
  }]);

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
        // Add comscore analytics
        var vote  = ( supported ) ? 'up_vote' : 'down_vote';
        udm_( 'http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&ns_type=hidden&ns_ui_type=clickin&name=consulta.documento.' + $scope.doc.slug + '&ns_vote=' + vote );

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
    };

    $(document).ready(function () {
      var annotator;
      var popup;

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

            createLoginPopup(event);
          }

        };
      }

      annotator = $('#doc_content').annotator({
        readOnly: !$scope.doc.is_opened
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
  }]);

angular.module( 'madisonApp.controllers' )
    .controller( 'UserNotificationsController', [ '$scope', '$http', 'UserService', function ( $scope, $http, UserService ) {
        //Wait for AppController controller to load user
        UserService.exists.then(function () {
            $http.get( '/api/user/' + $scope.user.id + '/notifications' )
                .success( function ( data ) {
                    $scope.notifications    = data;
                }).error( function ( data ) {
                    console.error( "Error loading notifications: %o", data );
                });
        });

        //Watch for notification changes
        $scope.$watch( 'notifications', function ( newValue, oldValue ) {
            if ( oldValue !== undefined ) {
                //Save notifications
                $http.put('/api/user/' + $scope.user.id + '/notifications', {
                            notifications   : newValue
                        })
                    .success( function ( data ) {
                        //Do nothing?
                    }).error( function ( data ) {
                        console.error( "Error updating notification settings: %o", data );
                    });
            }
        }, true );
    }]);
angular.module( 'madisonApp.controllers' )
    .controller( 'UserPageController', [ '$scope', '$http', '$location', function ( $scope, $http, $location ) {
        $scope.user         = {};
        $scope.meta         = '';
        $scope.docs         = [];
        $scope.activities   = [];
        $scope.verified     = false;

        $scope.init             = function () {
            $scope.getUser();
        };
        $scope.getUser          = function () {
            var abs = $location.absUrl();
            var id  = abs.match( /.*\/(\d+)$/ );
            id      = id[1];

            $http.get( '/api/user/' + id )
                .success( function ( data ) {
                    $scope.user = angular.copy( data );
                    $scope.meta = angular.copy( data.user_meta );

                    angular.forEach( data.docs, function ( doc ) {
                        $scope.docs.push( doc );
                    });
                    angular.forEach( data.comments, function ( comment ) {
                        comment.label   = 'comment';
                        $scope.activities.push( comment );
                    });
                    angular.forEach( data.annotations, function ( annotation ) {
                        annotation.label    = 'annotation';
                        $scope.activities.push( annotation );
                    });
                    angular.forEach( $scope.user.user_meta, function ( meta ) {
                        var cont = true;

                        if ( meta.meta_key === 'verify' && meta.meta_value === 'verified' && cont ) {
                            $scope.verified = true;
                            cont = false;
                        }
                    });
                }).error( function ( data ) {
                    console.error( "Unable to retrieve user: %o", data );
                });
        };
        $scope.showVerified     = function () {
            if ( $scope.user.docs && $scope.user.docs.length > 0 ) {
                return true;
            }

            return false;
        };
        $scope.activityOrder    = function ( activity ) {
            return Date.parse( activity.created_at );
        };
    }]);
angular.module( 'madisonApp.dashboardControllers', []);
angular.module('madisonApp.dashboardControllers')
    .controller('DashboardDocumentsController', ['$scope', '$http', '$filter', function ($scope, $http, $filter) {
        $scope.docs         = [];
        $scope.categories   = [];
        $scope.sponsors     = [];
        $scope.statuses     = [];
        $scope.dates        = [];
        $scope.dateSort     = '';
        $scope.select2      = '';
        $scope.docSort      = "created_at";
        $scope.reverse      = true;

        $scope.select2Config    = {
            multiple    : true,
            allowClear  : true,
            placeholder : "Filter documents by category, sponsor, or status"
        };
        $scope.dateSortConfig   = {
            allowClear  : true,
            placeholder : "Sort By Date"
        };

        //Retrieve all docs
        $http.get(_baseUrl + '/api/docs')
            .success(function (data) {
              $scope.parseDocs(data.results);
            })
            .error(function (data) {
                console.error("Unable to get documents: %o", data);
            });
        $scope.parseDocs    = function (docs) {
            angular.forEach(docs, function ( doc) {
                $scope.docs.push(doc);
                $scope.parseDocMeta(doc.categories, 'categories');
                $scope.parseDocMeta(doc.sponsor, 'sponsors');
                $scope.parseDocMeta(doc.statuses, 'statuses');

                angular.forEach(doc.dates, function (date) {
                    date.date   = Date.parse(date.date);
                });
            });
        };
        $scope.parseDocMeta = function (collection, name) {
            if (collection === undefined || collection.length === 0) {
                return;
            }

            angular.forEach(collection, function ( item) {
                var found = $filter('getById')($scope[name], item.id);

                if (found === null) {
                    switch (name) {
                        case 'categories':
                            $scope.categories.push(item);
                            break;
                        case 'sponsors':
                            $scope.sponsors.push(item );
                            break;
                        case 'statuses':
                            $scope.statuses.push(item);
                            break;
                        default:
                            console.error('Unknown meta name: ' + name);
                    }
                }
            });
        };
        $scope.docFilter    = function (doc) {
            var show = false;

            if ($scope.select2 !== undefined && $scope.select2 !== '') {
                var cont    = true;
                var select2 = $scope.select2.split('_');
                var type    = select2[0];
                var value   = parseInt(select2[1], 10);

                switch ( type) {
                    case 'category':
                        angular.forEach(doc.categories, function (category) {
                            if (   +category.id === value && cont) {
                                show    = true;
                                cont    = false;
                            }
                        });
                        break;
                    case 'sponsor':
                        angular.forEach(doc.sponsor, function (sponsor) {
                            if (+sponsor.id === value && cont) {
                                show    = true;
                                cont    = false;
                            }
                        });
                        break;
                    case 'status':
                        angular.forEach(doc.statuses, function ( status) {
                            if (+status.id === value && cont) {
                                show    = true;
                                cont    = false;
                            }
                        });
                        break;
                }
            } else {
                show    = true;
            }

            return show;
        };
    }]);

angular.module('madisonApp.dashboardControllers')
    .controller('DashboardEditorController', [ '$scope', '$http', '$timeout', '$location', '$filter', 'growl', function ($scope, $http, $timeout, $location, $filter, growl) {
        $scope.doc                  = {};
        $scope.sponsor              = {};
        $scope.group                = {};
        $scope.status               = {};
        $scope.newdate              = {
            label   : '',
            date    : new Date()
        };
        $scope.verifiedUsers        = [];
        $scope.categories           = [0];
        $scope.introtext            = "";
        $scope.suggestedCategories  = [];
        $scope.suggestedStatuses    = [];
        $scope.suggestedGroups      = [];
        $scope.dates                = [];

        $scope.init             = function () {
            var abs = $location.absUrl();
            var id  = abs.match(/.*\/(\d+)$/)[1];
            $scope.doc_id = id;

            function clean_slug(string) {
                return string.toLowerCase().replace(/[^a-zA-Z0-9\- ]/g, '').replace(/ +/g, '-');
            }

            var docDone = $scope.getDoc(id);

            $scope.getAllCategories();
            $scope.getVerifiedUsers();
            $scope.setSelectOptions();

            var initCategories  = true;
            var initIntroText   = true;
            var initSponsor     = true;
            var initStatus      = true;
            var initGroup       = true;
            var initTitle       = true;
            var initSlug        = true;
            var initContent     = true;

            docDone.then(function () {
                new Markdown.Editor(Markdown.getSanitizingConverter()).run();

                // We don't control the pagedown CSS, and this DIV needs to be scrollable
                $("#wmd-preview").css("overflow", "scroll");
                // Resizing dynamically according to the textarea is hard, so just set the height once (22 is padding)
                $("#wmd-preview").css("height", ($("#wmd-input").height() + 22));
                $("#wmd-input").scroll(function () {
                    $("#wmd-preview").scrollTop($("#wmd-input").scrollTop());
                });

                //Save intro text after a 3 second timeout
                var introTextTimeout    = null;
                $scope.updateIntroText  = function (newValue) {
                    if(introTextTimeout) {
                        $timeout.cancel(introTextTimeout);
                    }
                    introTextTimeout    = $timeout(function () {
                        $scope.saveIntroText(newValue);
                    }, 3000);
                };

                // $scope.getDocSponsor().then(function () {
                //     $scope.$watch('sponsor', function () {
                //         if (initSponsor) {
                //             $timeout(function () {
                //                 initSponsor = false;
                //             });
                //         } else {
                //             $scope.saveSponsor();
                //         }
                //     });
                // });
                $scope.getDocStatus().then(function () {
                    $scope.$watch('status', function () {
                        if (initStatus) {
                            $timeout(function () {
                                initStatus  = false;
                            });
                        } else {
                            $scope.saveStatus();
                        }
                    });
                });
                $scope.getDocGroup().then(function () {
                    $scope.$watch('group', function () {
                        if (initGroup) {
                            $timeout(function () {
                                initGroup  = false;
                            });
                        } else {
                            $scope.saveGroup();
                        }
                    });
                });
                $scope.getDocCategories().then(function () {
                    $scope.$watch('categories', function () {
                        if (initCategories) {
                            $timeout(function () {
                                initCategories  = false;
                            });
                        } else {
                            $scope.saveCategories();
                        }
                    });
                });
                $scope.getIntroText();
                $scope.getDocDates();

                $scope.$watch('doc.title', function () {
                    if (initTitle) {
                        $timeout(function () {
                            initTitle = false;
                        });
                    } else {
                        $scope.saveTitle();
                    }
                });
                $scope.$watch('doc.slug', function () {
                    if (initSlug) {
                        $timeout(function () {
                            initSlug = false;
                        });
                    } else {
                        // Changing doc.slug in-place will trigger the $watch
                        var safe_slug       = $scope.doc.slug;
                        var sanitized_slug  = clean_slug(safe_slug);
                        // If cleaning the slug didn't change anything, we have a valid NEW slug, and we can save it
                        if (safe_slug == sanitized_slug) {
                            $scope.saveSlug();
                        } else {
                            // Change the slug in-place, which will trigger another watch
                            // (handled by the POST function)
                            console.log('Invalid slug, reverting');
                            $scope.doc.slug = sanitized_slug;
                        }
                    }
                });

                // Save the content every 5 seconds
                var timeout     = null;
                $scope.$watch('doc.content.content', function () {
                    if (initContent) {
                        $timeout(function () {
                            initContent = false;
                        });
                    } else {
                        if (timeout) {
                            $timeout.cancel(timeout);
                        }
                        timeout     = $timeout(function () {
                            $scope.saveContent();
                        }, 5000);
                    }
                });
            });
        };
        /**
         * getShortUrl
         *
         * Makes API call to opngv.us/api
         * Runs when the 'Get Short Url' button is clicked on the 'Document Information' tab.
        */
        $scope.getShortUrl      = function () {
            /**
            * Hardcoded API Credentials
            */
            var opngv = {
                username    : 'madison-robot',
                password    : 'MeV3MJJE',
                api         : 'http://opngv.us/yourls-api.php'
            };

            //Construct document url
            var slug        = $scope.doc.slug;
            var long_url    = _baseUrl + '/docs/' + slug;

            $http({
                url     : opngv.api,
                method  : 'JSONP',
                params  : {
                    callback    : 'JSON_CALLBACK',
                    action      : 'shorturl',
                    format      : 'jsonp',
                    url         : long_url,
                    username    : opngv.username,
                    password    : opngv.password
                }
                }).success(function (data) {
                    $scope.short_url    = data.shorturl;
                }).error(function (data) {
                    console.error(data);
                    growl.error('There was an error generating your short url.');
                });
        };
        $scope.setSelectOptions = function () {

            $scope.categoryOptions    = {
                placeholder         : 'Agrega categorías del documento',
                multiple            : true,
                simple_tags         : true,
                tokenSeparators     : [","],
                tags                : function () {
                  return $scope.suggestedCategories;
                },
                initSelection       : function (element, callback) {
                  // Remove initial 0 on $scope.categories
                  $scope.categories.splice(0, 1);

                  // Get doc categories (There is a bug using multiple select with async data)
                  $http.get(_baseUrl + '/api/docs/' + $scope.doc_id + '/categories').success(function (data) {

                      // Construct $scope.categories
                      angular.forEach(data, function (category) {
                          $scope.categories.push(category.name + ' - ' + category.kind);
                      });

                      var returned    = [];

                      // Make sure $scope.categories only contains unique values
                      $scope.categories = $.unique( $scope.categories );

                      // Generate initSelection
                      angular.forEach($scope.categories, function (category, index) {
                          returned.push(angular.copy({
                              id      : index,
                              text    : category
                          }));
                      });

                      // Return initSelection
                      callback(returned);

                  }).error(function (data) {
                      console.error("Unable to get categories for document %o: %o", $scope.doc, data);
                  });
                }
            };

            /*jslint unparam: true*/
            $scope.statusOptions    = {
                placeholder         : 'Select Document Status',
                allowClear          : true,
                ajax                : {
                    url         : _baseUrl + '/api/docs/statuses',
                    dataType    : 'json',
                    data        : function (term, page) {
                        return;
                    },
                    results     : function (data, page) {
                        var returned    = [];

                        angular.forEach(data, function (status) {
                            returned.push({
                                id      : status.id,
                                text    : status.label
                            });
                        });
                        return {
                            results     : returned
                        };
                    }
                },
                data                : function () {
                    return $scope.suggestedStatuses;
                },
                results             : function () {
                    return $scope.status;
                },
                createSearchChoice  : function (term) {
                    return {
                        id      : term,
                        text    : term
                    };
                },
                initSelection       : function (element, callback) {
                    callback($scope.status);
                }
            };

            $scope.groupOptions    = {
                placeholder         : 'Select Document Group',
                allowClear          : true,
                ajax                : {
                    url         : _baseUrl + '/api/docs/groups',
                    dataType    : 'json',
                    data        : function (term, page) {
                        return;
                    },
                    results     : function (data, page) {
                        var returned    = [];

                        angular.forEach(data, function (group) {
                            returned.push({
                                id      : group.id,
                                text    : group.name
                            });
                        });
                        return {
                            results     : returned
                        };
                    }
                },
                data                : function () {
                    return $scope.suggestedGroups;
                },
                results             : function () {
                    return $scope.group;
                },
                createSearchChoice  : function (term) {
                    return {
                        id      : term,
                        text    : term
                    };
                },
                initSelection       : function (element, callback) {
                    callback($scope.group);
                }
            };

            $scope.sponsorOptions   = {
                placeholde          : 'Select Document Sponsor',
                allowClear          : true,
                ajax                : {
                    url         : _baseUrl + '/api/user/sponsors/all',
                    dataType    : 'json',
                    data        : function () {
                        return;
                    },
                    results     : function (data) {
                        var returned = [];

                        if(!data.success) {
                            alert(data.message);
                            return;
                        }

                        angular.forEach(data.sponsors, function (sponsor) {
                            var text    = "";

                            switch(sponsor.sponsorType) {
                                case 'group':
                                    text    = "[Group] " + sponsor.name;
                                    break;
                                case 'user':
                                    text    = sponsor.fname + " " + sponsor.lname + " - " + sponsor.email;
                                    break;
                            }

                            returned.push({
                                id      : sponsor.id,
                                type    : sponsor.sponsorType,
                                text    : text
                            });
                        });

                        return {
                            results: returned
                        };
                    }
                },
                initSelection       : function (element, callback) {
                    callback($scope.sponsor);
                }
            };
            /*jslint unparam: false*/
        };
        $scope.statusChange     = function (status) {
            $scope.status   = status;
        };
        $scope.sponsorChange    = function (sponsor ) {
            $scope.sponsor  = sponsor;
        };
        $scope.groupChange    = function (group) {
            $scope.group  = group;
        };
        $scope.categoriesChange = function (categories) {
            $scope.categories   = categories;
        };
        $scope.getDoc           = function (id) {
            return $http.get(_baseUrl + '/api/docs/' + id)
                .success(function (data) {
                    $scope.doc  = data;
            });
        };
        $scope.saveTitle        = function () {
            return $http.post(_baseUrl +  '/api/docs/' + $scope.doc.id + '/title', {
                'title' : $scope.doc.title
            })
                .success(function (data) {
                    console.log("Title saved successfully: %o", data);
                }).error(function (data) {
                    console.error("Error saving title for document:", data);
                });
        };
        $scope.saveSlug         = function () {
            return $http.post(_baseUrl +  '/api/docs/' + $scope.doc.id + '/slug', {
                'slug'  : $scope.doc.slug
            })
                .success(function (data) {
                    console.log(_baseUrl + "Slug sent: %o", data);
                }).error(function (data) {
                    console.error("Error saving slug for document:", data);
                });
        };
        $scope.saveContent      = function () {
            return $http.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/content', {
                'content'   : $scope.doc.content.content
            })
                .success(function (data) {
                    console.log("Content saved successfully: %o", data);
                }).error(function (data) {
                    console.error("Error saving content for document:", data);
                });
        };
        $scope.createDate       = function (newDate) {
            if ($scope.newdate.label !== '') {
                $scope.newdate.date = $filter('date')(newDate, 'yyyy-MM-ddTHH:mm:ssZ');

                $http.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/dates', {
                    date    : $scope.newdate
                })
                    .success(function (data) {
                        data.date       = Date.parse(data.date);
                        data.$changed   = false;
                        $scope.dates.push(data);

                        $scope.newdate  = {
                            label   : '',
                            date    : new Date()
                        };
                    }).error(function (data) {
                        console.error("Unable to save date: %o", data);
                    });
            }
        };
        $scope.deleteDate       = function (date) {
            $http['delete'](_baseUrl + '/api/docs/' + $scope.doc.id + '/dates/' + date.id)
                .success(function () {
                    var index   = $scope.dates.indexOf(date);
                    $scope.dates.splice(index, 1);
                }).error(function () {
                    console.error("Unable to delete date: %o", date);
                });
        };
        $scope.saveDate         = function (date) {
            var sendDate    = angular.copy(date);
            sendDate.date   = $filter('date')(sendDate.date, 'yyyy-MM-ddTHH:mm:ssZ');

            return $http.put(_baseUrl + '/api/dates/' + date.id, {
                date    : sendDate
            })
                .success(function (data) {
                    date.$changed   = false;
                    console.log("Date saved successfully: %o", data);
                }).error(function (data) {
                    console.error("Unable to save date: %o (%o)", date, data);
                });
        };
        $scope.getDocDates      = function () {
            return $http.get(_baseUrl + '/api/docs/' + $scope.doc.id + '/dates')
                .success(function (data) {
                    angular.forEach(data, function (date, index) {
                        date.date       = Date.parse(date.date.replace(/-/g, '/'));
                        date.$changed   = false;
                        $scope.dates.push(angular.copy(date));

                        $scope.$watch('dates[' + index + ']', function (newitem, olditem) {
                            if (!angular.equals(newitem, olditem) && newitem !== undefined) {
                                newitem.$changed = true;
                            }
                        }, true);
                    });
                }).error(function (data) {
                    console.error("Error getting dates: %o", data);
                });
        };
        $scope.getVerifiedUsers = function () {
            return $http.get(_baseUrl + '/api/user/verify')
                .success(function (data) {
                    angular.forEach(data, function (verified) {
                        $scope.verifiedUsers.push(angular.copy(verified.user));
                    });
                }).error(function (data) {
                    console.error("Unable to get verified users: %o", data);
                });
        };
        $scope.getDocCategories = function () {
            return $http.get(_baseUrl + '/api/docs/' + $scope.doc.id + '/categories')
                .success(function (data) {
                    angular.forEach(data, function ( category) {
                        $scope.categories.push(category.name + ' - ' + category.kind);
                    });
                }).error(function (data) {
                    console.error("Unable to get categories for document %o: %o", $scope.doc, data);
                });
        };
        $scope.getIntroText     = function () {
            return $http.get(_baseUrl + '/api/docs/' + $scope.doc.id + '/introtext')
                .success(function (data) {
                    $scope.introtext    = data.meta_value;
                }).error(function (data) {
                    console.error("Unable to get Intro Text for document %o: %o", $scope.doc, data);
                });
        };
        $scope.getDocSponsor    = function () {
            return $http.get(_baseUrl + '/api/docs/' + $scope.doc.id + '/sponsor')
                .success(function (data) {
                    if(data.sponsorType === undefined){
                        $scope.sponsor = null;
                        return;
                    }

                    var text = "";
                    switch(data.sponsorType.toLowerCase()) {
                        case 'group':
                            text    = "[Group] " + data.name;
                            break;
                        case 'user':
                            text    = data.fname + " " + data.lname + " - " + data.email;
                            break;
                    }

                    $scope.sponsor  = {
                        id      : data.id,
                        type    :  data.sponsorType.toLowerCase(),
                        text    : text
                    };
                }).error(function (data) {
                    console.error("Error getting document sponsor: %o", data);
                });
        };
        $scope.getDocGroup     = function () {
            return $http.get(_baseUrl + '/api/docs/' + $scope.doc.id + '/group')
                .success(function (data) {
                    if (data.id === undefined) {
                        $scope.group = null;
                    } else {
                        $scope.group = {
                            id      : data.id,
                            text    : data.name
                        };
                    }
                }).error(function (data) {
                    console.error("Error getting document group: %o", data);
                });
        };
        $scope.getDocStatus     = function () {
            return $http.get(_baseUrl + '/api/docs/' + $scope.doc.id + '/status')
                .success(function (data) {
                    if (data.id === undefined) {
                        $scope.status = null;
                    } else {
                        $scope.status = {
                            id      : data.id,
                            text    : data.label
                        };
                    }
                }).error(function (data) {
                    console.error("Error getting document status: %o", data);
                });
        };
        $scope.getAllStatuses   = function () {
            $http.get(_baseUrl + '/api/docs/statuses')
                .success(function (data) {
                    angular.forEach(data, function (status) {
                        $scope.suggestedStatuses.push(status.label);
                    });
                }).error(function (data) {
                    console.error("Unable to get document statuses: %o", data);
                });
        };
        $scope.getAllGroups   = function () {
            $http.get(_baseUrl + '/api/docs/groups')
                .success(function (data) {
                    angular.forEach(data, function (status) {
                        $scope.suggestedGroups.push(status.label);
                    });
                }).error(function (data) {
                    console.error("Unable to get document groups: %o", data);
                });
        };
        $scope.getAllCategories = function () {
            return $http.get(_baseUrl + '/api/docs/categories')
                .success(function (data) {
                    angular.forEach(data, function (category) {
                        $scope.suggestedCategories.push(category.name + ' - ' + category.kind);
                    });
                })
                .error(function (data) {
                    console.error("Unable to get document categories: %o", data);
                });
        };
        $scope.saveStatus       = function () {
            return $http.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/status', {
                status  : $scope.status
            })
                .success(function (data) {
                    console.log("Status saved successfully: %o", data);
                }).error(function (data) {
                    console.error("Error saving status: %o", data);
                });
        };
        $scope.saveGroup       = function () {
            return $http.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/group', {
                group  : $scope.group
            })
                .success(function (data) {
                    console.log("Group saved successfully: %o", data);
                }).error(function (data) {
                    console.error("Error saving group: %o", data);
                });
        };
        $scope.saveSponsor      = function () {
            return $http.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/sponsor', {
                'sponsor'   : $scope.sponsor
            })
                .success(function (data) {
                    console.log("Sponsor saved successfully: %o", data);
                }).error(function (data) {
                    console.error("Error saving sponsor: %o", data);
                });
        };
        $scope.saveCategories   = function () {
            return $http.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/categories', {
                'categories'    : $scope.categories
            })
                .success(function (data) {
                    if(typeof data.status !== 'undefined' && data.status == 'error'){
                      angular.forEach(data.messages, function (message) {
                        growl.error(message.text);
                      });
                      console.log("Error saving categories for document: %o", data);
                    }else{
                      console.log("Categories saved successfully: %o", data);
                    }
                }).error(function (data) {
                    console.error("Error saving categories for document %o: %o \n %o", $scope.doc, $scope.categories, data);
                });
        };
        //Triggered 5 seconds after last change to textarea with ng-model="introtext"
        $scope.saveIntroText    = function (introtext) {
            return $http.post(_baseUrl + '/api/docs/' + $scope.doc.id + '/introtext', {
                'intro-text'    : introtext
            })
                .success(function (data) {
                    console.log("Intro Text saved successfully: %o", data);
                }).error(function (data) {
                    console.error("Error saving intro text for document %o: %o", $scope.doc, $scope.introtext);
                });
        };
    }]);

angular.module( 'madisonApp.dashboardControllers' )
    .controller( 'DashboardSettingsController', [ '$scope', '$http', function ( $scope, $http ) {
        $scope.admins   = [];

        $scope.getAdmins    = function () {
            $http.get( '/api/user/admin' )
                .success( function ( data ) {
                    $scope.admins   = data;
                })
                .error( function ( data ) {
                    console.error( data );
                });
        };
        $scope.saveAdmin    = function ( admin ) {
            admin.saved = false;

            $http.post( '/api/user/admin', {
              'admin'   : admin
            })
                .success( function () {
                    admin.saved = true;
                })
                .error( function ( data ) {
                    console.error( data );
                });
        };
        $scope.init         = function () {
            $scope.getAdmins();
        };
    }]);
angular.module( 'madisonApp.dashboardControllers' )
    .controller( 'DashbordVerifyController', [ '$scope', '$http', function ( $scope, $http ) {
        $scope.requests     = [];

        $scope.init         = function () {
            $scope.getRequests();
        };
        $scope.getRequests  = function () {
            $http.get( '/api/user/verify' )
                .success( function ( data ) {
                    $scope.requests = data;
                })
                .error( function ( data ) {
                    console.error( data );
                });
        };
        $scope.update       = function ( request, status ) {
            $http.post( '/api/user/verify', {
              'request' : request,
              'status'  : status
            })
                .success( function () {
                    request.meta_value  = status;
                })
                .error( function ( data ) {
                    console.error( data );
                });
        };
    }]);
angular.module( 'madisonApp.dashboardControllers' )
    .controller( 'DashboardVerifyGroupController', [ '$scope', '$http', function ( $scope, $http ) {
        $scope.requests     = [];

        $scope.init         = function() {
            $scope.getRequests();
        };
        $scope.getRequests  = function() {
            $http.get( '/api/groups/verify' )
                .success( function( data, status, headers, config ){
                    $scope.requests = data;
                })
                .error( function( data, status, headers, config ){
                    console.error( data );
                });
        };
        $scope.update       = function( request, status, event ) {
            $http.post( '/api/groups/verify', {
                'request'   : request,
                'status'    : status
            })
                .success( function( data ){
                    request.status  = status;
                })
                .error( function( data, status, headers, config ){
                    console.error( data );
                });
        };
    }]);
angular.module( 'madisonApp.dashboardControllers' )
    .controller( 'DashboardVerifyUserController', [ '$scope', '$http', function ( $scope, $http ) {
        $scope.requests = [];

        $scope.init         = function() {
            $scope.getRequests();
        };
        $scope.getRequests  = function() {
            $http.get( '/api/user/independent/verify' )
                .success(function( data, status, headers, config ){
                    $scope.requests = data;
                })
                .error( function( data, status, headers, config ){
                    console.error( data );
                });
        };
        $scope.update       = function( request, status, event ) {
            $http.post( '/api/user/independent/verify', {
                'request'   : request,
                'status'    : status
            })
                .success( function( data ){
                    request.meta_value  = status;
                    location.reload();
                })
                .error( function( data, status, headers, config ){
                    console.error( data );
                });
        };
    }]);
angular.module( 'madisonApp.resources', []);
angular.module('madisonApp.resources')
    .factory('Doc', ['$resource', function($resource) {
        return $resource(_baseUrl + "/api/docs/:id", null, {
            query: {
                method  : 'GET',
                isArray : false
            }
        });
    }]);

angular.module( 'madisonApp.services', []);

var messageTimer;
function feedbackMessage( message, type, container ) {
    type            = typeof type !== 'undefined' ? type : 'info';
    container       = typeof container !== 'undefined' ? container : '.message-box';

    var html        = '<div class="alert alert-' + type + '">' + message + '</div>';
    var $container  = $( container ).first();
    $container.fadeIn( "fast" );
    $container.append( html );

    clearTimeout( messageTimer );
    messageTimer    = setTimeout( clearMessages, 6000, container );
}

function clearMessages( container ) {
    var $container  = $( container ).first();
    $container.fadeOut( "slow", function() {
        $container.html( '' );
    });
}
angular.module( 'madisonApp.services' )
    .factory( 'annotationService', [ '$rootScope', '$sce', function ( $rootScope, $sce ) {
        var annotationService           = {};
        var converter                   = new Markdown.Converter();
        annotationService.annotations   = [];

        annotationService.setAnnotations    = function ( annotations ) {
            angular.forEach(annotations, function ( annotation ) {
                annotation.html = $sce.trustAsHtml( converter.makeHtml( annotation.text ) );
                this.annotations.push( annotation );
            }, this );

            this.broadcastUpdate();
        };
        annotationService.addAnnotation     = function ( annotation ) {
            if ( annotation.id === undefined ) {
                var interval    = window.setInterval( function () {
                    this.addAnnotation( annotation );
                    window.clearInterval( interval );
                }.bind(this), 500 );
            } else {
                annotation.html = $sce.trustAsHtml(converter.makeHtml(annotation.text));
                this.annotations.push(annotation);
                this.broadcastUpdate();
            }
        };
        annotationService.broadcastUpdate   = function () {
            $rootScope.$broadcast( 'annotationsUpdated' );
        };

        return annotationService;
    }]);
angular.module('madisonApp.services')
    .factory('createLoginPopup', ['$document', '$timeout', 'growl', function ($document, $timeout, growl) {
        var body            = $document.find('body');
        var html            = $document.find('html');
        var attach_handlers = function () {
            html.on('click.popup', function () {
                $('.popup').remove();
                html.off('click.popup');
            });
        };
        var ajaxify_form    = function (inForm, callback) {
                var form    = $(inForm);
                form.submit(function (e) {
                e.preventDefault();

                $.post(form.attr('action'), form.serialize(), function (response) {
                    if (response.errors && Object.keys(response.errors).length) {
                        var error_html = $('<ul></ul>');

                        /*jslint unparam:true*/
                        angular.forEach(response.errors, function (value, key) {
                            //** If growl notifications are prefered
                            // growl.error(value[0]);
                            //** If growl notifications are prefered
                            error_html.append('<li>' + value + '</li>');
                        });
                        /*jslint unparam:false*/

                        form.find('.errors').html(error_html);
                    } else {
                        callback(response);
                    }
                });
            });
        };

        return function LoginPopup(event) {
            var popup   = $('<div class="popup unauthed-popup"><p>Por favor regístrate.</p>' +
                '<input type="button" id="login" value="Ingresar" class="btn btn-primary"/>' +
                '<input type="button" id="signup" value="Registrarse" class="btn btn-primary" /></div>');


            popup.on('click.popup', function ( event) {
                event.stopPropagation();
            });

            $('#login', popup).click(function (event) {
                event.stopPropagation();
                event.preventDefault();

                $.get(_baseUrl + '/api/auth/login', {}, function (data) {
                    data    = $(data);

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

                $.get(_baseUrl + '/api/auth/signup', {}, function (data) {
                    data    = $(data);

                    ajaxify_form(data.find('form'), function ( result) {
                        $('html').trigger('click.popup');
                        alert(result.message);
                    });

                    popup.html(data);
                });
            });
            body.append(popup);

            var position    = {
                'top'   : event.pageY - popup.height(),
                'left'  : event.clientX
            };
            popup.css(position).css('position', 'absolute');
            popup.css('z-index', '999');

            $timeout(function () {
                attach_handlers();
            }, 50);
        };
    }]);

angular.module( 'madisonApp.services' )
    .service( 'modalService', [ '$modal', function ( $modal ) {
        //Set modal defaults
        var modalDefaults   = {
            backdrop            : true,
            keyboard            : true,
            modalFade           : true,
            templateUrl         : '/consulta-public/templates/modal.html'
        };
        var modalOptions    = {
            closeButtonText     : 'Close',
            actionButtonText    : false,
            headerText          : 'Notice',
            bodyText            : 'Hmm... someone forgot the content here...'
        };

        this.showModal  = function ( customModalDefaults, customModalOptions ) {
            if ( !customModalDefaults ) {
                customModalDefaults     = {};
            }
            //Accepts either true or 'static'.  'static' doesn't close the modal on click.
            customModalDefaults.backdrop = true;

            return this.show( customModalDefaults, customModalOptions );
        };
        this.show       = function ( customModalDefaults, customModalOptions ) {
            //Create temp objects to work with since we're in a singleton service
            var tempModalDefaults   = {};
            var tempModalOptions    = {};

            //Map angular-ui modal custom defaults to modal defaults defined in service
            angular.extend( tempModalDefaults, modalDefaults, customModalDefaults );
            //Map modal.html $scope custom properties to defaults defined in service
            angular.extend( tempModalOptions, modalOptions, customModalOptions );

            if ( !tempModalDefaults.controller ) {
                tempModalDefaults.controller    = function ( $scope, $modalInstance ) {
                    $scope.modalOptions         = tempModalOptions;
                    $scope.modalOptions.ok      = function ( result ) {
                        $modalInstance.close( result );
                    };
                    $scope.modalOptions.close   = function ( result ) {
                        $modalInstance.dismiss( 'cancel' );
                    };
                };
            }

            return $modal.open( tempModalDefaults ).result;
        };
    }]);

angular.module('madisonApp.services')
    .factory('UserService', ['$rootScope', '$http', function ($rootScope, $http) {
        var UserService  = {};
        UserService.user = {};

        UserService.getUser = function () {
            UserService.exists = $http.get(_baseUrl + '/api/user/current')
                .success(function ( data ) {
                    UserService.user = data.user;
                    $rootScope.$broadcast('userUpdated');
                });
        };

        return UserService;
    }]);

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
    $('.inline-diff-visible').show();
    getDiff('inline_diff_result');
  }

  function getSideDiff() {
    $('.side-diff-visible').show();
    $('.inline-diff-visible').hide();
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

angular.module( 'madisonApp.directives', []);
angular.module( 'madisonApp.directives' )
    .directive( 'activitySubComment', [ 'growl', '$anchorScroll', '$timeout', function ( growl, $anchorScroll, $timeout ) {
        return {
            restrict    : 'A',
            transclude  : true,
            templateUrl : '/consulta-public/templates/activity-sub-comment.html',
            compile     : function () {
                return {
                    post: function ( scope, element, attrs ) {
                        var commentLink = element.find( '.subcomment-link' ).first();
                        var linkPath    = _currentPath + '#annsubcomment_' + attrs.subCommentId;
                        $( commentLink ).attr( 'data-clipboard-text', linkPath );

                        var client      = new ZeroClipboard( commentLink );
                        client.on( 'aftercopy', function ( event ) {
                            scope.$apply( function () {
                                growl.success( "Link copied to clipboard." );
                            });
                        });

                        $timeout( function () {
                            $anchorScroll();
                        }, 0 );
                    }
                };
            }
        };
    }]);

angular.module( 'madisonApp.directives' )
    .directive( 'annotationItem', [ 'growl', function ( growl ) {
        return {
            restrict    : 'A',
            transclude  : true,
            templateUrl : '/consulta-public/templates/annotation-item.html',
            compile     : function () {
                return {
                    post    : function ( scope, element, attrs ) {
                        var commentLink = element.find( '.comment-link' ).first();
                        var linkPath    = _currentPath + '#' + attrs.activityItemLink;
                        $( commentLink ).attr( 'data-clipboard-text', linkPath );

                        var client      = new ZeroClipboard( commentLink );
                        client.on( 'aftercopy', function ( event ) {
                            scope.$apply( function () {
                                growl.success( "Link copied to clipboard." );
                            });
                        });

                        var $span       = $( element ).find( '.activity-actions > span.ng-binding' );
                        $span.on( "click", function() {
                            var $feedbackElement    = $( this ).closest( '.activity-item' );
                            var prevBackground      = $feedbackElement.css( 'background' );
                            $feedbackElement.css( "background", "#2276d7" );
                            setTimeout( function() {
                                $feedbackElement.css( "background", prevBackground );
                            }, 500 );
                        });
                    }
                };
            }
        };
    }]);

angular.module( 'madisonApp.directives' )
    .directive( 'commentItem', [ 'growl', function ( growl ) {
        return {
            restrict    : 'A',
            transclude  : true,
            templateUrl : '/consulta-public/templates/comment-item.html',
            compile     : function () {
                return {
                    post: function ( scope, element, attrs ) {
                        var commentLink = element.find( '.comment-link' ).first();
                        var linkPath    = _currentPath + '#' + attrs.activityItemLink;
                        $( commentLink ).attr( 'data-clipboard-text', linkPath );

                        var client      = new ZeroClipboard( commentLink );
                        client.on( 'aftercopy', function ( event ) {
                            scope.$apply( function () {
                                growl.success( "Link copied to clipboard 2." );
                            });
                        });

                        var $span       = $( element ).find( '.activity-icon > span.ng-binding' );
                        $span.on( "click", function() {
                            $( element ).parent().effect( "highlight",{
                                color   : "#2276d7"
                            }, 1000 );
                        });
                    }
                };
            }
        };
    }]);

angular.module( 'madisonApp.directives' )
    .directive( 'docComments', function () {
        return {
            restrict    : 'AECM',
            templateUrl : '/consulta-public/templates/doc-comments.html'
        };
    });

angular.module( 'madisonApp.directives' )
    .directive( 'docLink', [ '$http', '$compile', function ( $http, $compile ) {
        return {
            restrict    : 'AECM',
            link        : function ( scope, elem, attrs ) {
                $http.get( '/api/docs/' + attrs.docId )
                    .success( function ( data ) {
                        var html    = '<a href="/docs/' + data.slug + '">' + data.title + '</a>';
                        var e       = $compile( html )( scope );
                        elem.replaceWith( e );
                    }).error( function ( data ) {
                        console.error( "Unable to retrieve document %o: %o", attrs.docId, data );
                    });
            }
        };
    }]);
angular.module( 'madisonApp.directives' )
    .directive( 'docListItem', function() {
        return {
            restrict    : 'A',
            templateUrl : '/consulta-public/templates/doc-list-item.html'
        };
    });

angular.module( 'madisonApp.directives' )
    .directive( 'ngBlur', function () {
        return function ( scope, elem, attrs ) {
            elem.bind( 'blur', function () {
                scope.$apply( attrs.ngBlur );
            });
        };
    });
angular.module( 'madisonApp.directives' )
    .directive( 'profileCompletionMessage', [ '$http', function ( $http ) {
        return {
            restrict    : 'A',
            templateUrl : '/consulta-public/templates/profile-completion-message.html',
            link        : function ( scope ) {
                scope.updateEmail   = function ( newEmail, newPassword ) {
                    //Issue PUT request to update user
                    $http.put(_baseUrl + '/api/user/' + scope.user.id + '/edit/email', {
                        email       : newEmail,
                        password    : newPassword
                    })
                        .success( function () {
                            //Note: Growl message comes from server response
                            scope.user.email = newEmail;
                        }).error( function ( data ) {
                            console.error( "Error updating user email: %o", data );
                            $('.update-email-error').html(data.messages[0].text);
                        });
                };
            }
        };
    }]);

angular.module( 'madisonApp.directives' )
    .directive( 'subcommentLink', [ 'growl', '$anchorScroll', '$timeout', function ( growl, $anchorScroll, $timeout ) {
        return {
            restrict    : 'A',
            template    : '<span class="glyphicon glyphicon-link" title="Copy link to clipboard"></span>',
            compile     : function () {
                return {
                    post    : function ( scope, element, attrs ) {
                        var commentLink = element;
                        var linkPath    = _currentPath + '#subcomment_' + attrs.subCommentId;
                        $( commentLink ).attr( 'data-clipboard-text', linkPath );

                        var client      = new ZeroClipboard( commentLink );
                        client.on( 'aftercopy', function ( event ) {
                            scope.$apply( function () {
                                growl.success( "Link copied to clipboard." );
                            });
                        });

                        $timeout( function () {
                            $anchorScroll();
                        }, 0 );

                        var $span       = $( element ).closest( '.activity-icon' ).children( 'span.ng-binding' );
                        $span.on( "click", function() {
                            $( element ).closest( '.activity-reply' ).effect( "highlight", {
                                color   : "#2276d7"
                            }, 1000 );
                        });
                    }
                };
            }
        };
    }]);

angular.module( 'madisonApp.filters', []);
angular.module( 'madisonApp.filters' )
    .filter( 'getById', function () {
        return function ( input, id ) {
            var i   = 0;
            var len = input.length;
            for ( i; i < len; i++ ) {
                if ( +input[i].id === +id ) {
                    return input[i];
                }
            }

            return null;
        };
    });
angular.module( 'madisonApp.filters' )
    .filter( 'gravatar', function () {
        return function ( email ) {
            var hash = '';
            if ( email !== undefined ) {
                hash = CryptoJS.MD5( email.toLowerCase() );
            }

            return hash;
        };
    });
angular.module( 'madisonApp.filters' )
    .filter( 'parseDate', function () {
        return function ( date ) {
            if(typeof date === 'string') {
                date = date.replace(/-/g, '/');
            }
            return Date.parse( date );
        };
    });

angular.module( 'madisonApp.filters' )
    .filter( 'toArray', function () {
        return function ( obj ) {
            if ( !( obj instanceof Object ) ) {
                return obj;
            }
            return _.map( obj, function ( val, key ) {
                val.$key    = key;
                return val;
            });
        };
    });
//var angular = require('angular');

window.getAnnotationService = function () {
  var elem = angular.element($('html'));
  var injector = elem.injector();
  var annotationService = injector.get('annotationService');

  return annotationService;
};

/*global window*/
window.jQuery = window.$;
$(function() {
  // Ajax Setup
  $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
      var token;
      if (! options.crossDomain) {
          token = $('meta[name="token"]').attr('content');
          if (token) {
              jqXHR.setRequestHeader('X-CSRF-Token', token);
          }
      }

      return jqXHR;
  });
});

var imports = [
    'madisonApp.filters',
    'madisonApp.services',
    'madisonApp.resources',
    'madisonApp.directives',
    'madisonApp.controllers',
    'madisonApp.dashboardControllers',
    'ui',
    'ui.bootstrap',
    'ui.bootstrap.datetimepicker',
    'ui.bootstrap.pagination',
    'ui.select',
    'ngAnimate',
    'ngCookies',
    'ngSanitize',
    'angular-growl',
    'ngResource',
    'ngRoute',
    'ipCookie',
    'pascalprecht.translate'
  ];

moment.locale('es');

var app = angular.module('madisonApp', imports);

// Add a prefix to all http calls
// app.config(function ($httpProvider) {
//   $httpProvider.interceptors.push(function ($q) {
//     return {
//       request: function (request) {
//         var doNotPrefix = [
//           'subcomment_renderer.html',
//           'template/',
//           'tour/'
//         ];
//         var shouldWeAvoidPrefix = function(element, index) {
//           return request.url.indexOf(element) > -1;
//         };
//
//         if ($.grep(doNotPrefix, shouldWeAvoidPrefix).length > 0) {
//           // templates included in angular-bootstrap
//           // e.g. angular.module("template/tabs/tabset.html",[])
//           // or defined as ng-templates
//         } else if (request.url.indexOf("templates/") < 0) {
//           request.url = "/consulta/" + request.url;
//           request.url = request.url.replace(/\/\//g, "/");
//         } else {
//           request.url = "/" + request.url;
//           request.url = request.url.replace(/\/\//g, "/");
//         }
//         return request || $q.when(request);
//       }
//     };
//   });
// });

app.config(['growlProvider', '$httpProvider', function (growlProvider, $httpProvider) {
    //Set up growl notifications
    growlProvider.messagesKey("messages");
    growlProvider.messageTextKey("text");
    growlProvider.messageSeverityKey("severity");
    growlProvider.onlyUniqueMessages(true);
    growlProvider.globalTimeToLive(5000);
}]);

app.config(function ($locationProvider) {
  $locationProvider.html5Mode(true);
});

app.config(['$translateProvider', function ($translateProvider) {
  // $translateProvider.useSanitizeValueStrategy('sanitize');

  $translateProvider.translations('en', {
    'POSTED': 'Posted',
    'UPDATED': 'Updated'
  });

  $translateProvider.translations('es', {
    'POSTED': 'Publicación',
    'UPDATED': 'Última actualización'
  });

  $translateProvider.preferredLanguage('es');
}]);

window.console = window.console || {};
window.console.log = window.console.log || function () {};

function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
}

//# sourceMappingURL=app.js.map