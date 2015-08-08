<div id="participate-comment-message" class="participate-vote-message message-box"></div>
@if($doc->is_opened())
  <div ng-init="documentopened=true"></div>
@elseif($doc->is_closed_for_comments())
  <div ng-init="documentclosedcomments=true"></div>
@elseif($doc->is_closed())
  <div ng-init="documentclosed=true"></div>
@endif
@if($loggedUser)
  @if($doc->canUserEdit($loggedUser))
    <div ng-init="caneditdocument=true"></div>
  @endif
  <div id="participate-comment" class="participate-comment">
    @if($doc->is_opened())
      @include('doc.reader.cofemer.comment')
    @else
      @if($doc->is_closed_for_comments())
        <p>{{ trans('messages.closedcommentsdoc') }}</p>
      @else
        <p>{{ trans('messages.closeddoc') }}</p>
      @endif
    @endif
  </div>
@endif
<div id="participate-activity" class="participate-activity">
	<h3>@{{ layoutTexts.header }}</h3>
  <p>@{{ layoutTexts.callToAction }}</p>
	<div class="activity-thread">
    <div id="@{{ 'comment_' + comment.id }}" class="activity-item" ng-repeat="comment in comments | orderBy:activityOrder:true track by $id(comment)" ng-class="comment.label">
      <div comment-item activity-item-link="@{{ comment.link }}"></div>
    </div>
	</div>
</div>
