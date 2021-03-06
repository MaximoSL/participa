<div id="participate-activity-message" class="participate-activity-message message-box"></div>
<div id="participate-activity-annotations" class="participate-activity">
	<div class="activity-thread">
		<div ng-hide="annotations.length">
			{{ trans('messages.noannotations') }}
		</div>
    	<div class="row" ng-repeat="annotation in annotations | orderBy:activityOrder:true track by $id(annotation)" ng-class="annotation.label">
        	<div annotation-item activity-item-link="@{{ annotation.link }}"></div>
    	</div>
	</div>
</div>
