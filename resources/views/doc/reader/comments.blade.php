<div id="participate-comment-message" class="participate-comment-message message-box"></div>
@if($loggedUser)
  @if($doc->canUserEdit($loggedUser))
    <div ng-init="caneditdocument=true"></div>
  @endif
  <div id="participate-comment" class="participate-comment">
  	@include('doc.reader.comment')
  </div>
@else
<div id="participate-comment" class="participate-comment">
	<p>{{ trans('messages.please') }} <a href="{{ route('auth.login') }}">{{ trans('messages.login') }}</a> {{ trans('messages.tocomment') }}.</p>
</div>
@endif
<div id="participate-activity" class="participate-activity">
	<h3>{{ trans('messages.comments') }}</h3>
	<div class="activity-thread">
    <div id="@{{ 'comment_' + comment.id }}" class="activity-item" ng-repeat="comment in comments | orderBy:activityOrder:true track by $id(comment)" ng-class="comment.label">
      <div comment-item activity-item-link="@{{ comment.link }}"></div>
    </div>
	</div>
</div>
