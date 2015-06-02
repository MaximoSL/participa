<div class="list-item">
  <div class="list-item-header">
    <div class="row">
      <div class="col-md-2">
        <div class="list-item-date">
          <div class="month">{{ $doc->created_at }}</div>
          <div class="day">{{ $doc->created_at }}</div>
          <div class="year">{{ $doc->created_at }}</div>
        </div>
      </div>
      <div class="col-md-9">
        <a class="list-item-title" href="{{ route('docs.doc', $doc->slug) }}" title="{{ $doc->title }}">{{ $doc->title }}</a>
      </div>
      <div class="col-md-1">
        <a class="list-item-arrow-link" href="{{ route('docs.doc', $doc->slug) }}" title="{{ $doc->title }}">
          <span class="glyphicon glyphicon-chevron-right"></span>
        </a>
      </div>
    </div>
  </div>
  <div class="list-item-footer">
    <div class="categories">
      @foreach($doc->categories as $category)
      <span class="category">{{ $category->name }}</span>
      @endforeach
    </div>
    <div class="updated-at">
      <span>{{ trans('messages.updated') }}: <b>{{ $doc->updated_at }}</b></span>
    </div>
  </div>
</div>
