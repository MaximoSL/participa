<?php
  $breadcrumbs = (empty($breadcrumbs)) ? [] : $breadcrumbs;
  $breadcrumbs_class = (empty($breadcrumbs_class)) ? '' : $breadcrumbs_class;
?>

<div class="row secondary-nav">
  <div class="col-sm-4 col-md-3 text-right pull-right">
    @include('partials._nav-dropdown')
  </div>
  <br class="clear-both visible-xs">
  <br class="clear-both visible-xs">
  <div class="col-sm-8 col-md-9">
    <ol class="breadcrumb no-margin-bottom no-margin-top {{ $breadcrumbs_class }}">
      <li>
        <a href="{{ route('home') }}" target="_self">
          <i class="icon icon-home"></i> {{ trans('messages.home')}}
        </a>
      </li>
      @foreach($breadcrumbs as $breadcrumb)
        <?php
          $breadcrumb['params'] = (empty($breadcrumb['params'])) ? [] : $breadcrumb['params'];
        ?>
        <li>
          <a href="{{ route($breadcrumb['route'], $breadcrumb['params']) }}" target="_self">
            {{ $breadcrumb['label'] }}
          </a>
        </li>
      @endforeach
    </ol>
  </div>
</div>
