<section class="pagination center {{count($layout['pages']) > 1 ? '' : 'hidden'}}">
    <div class="previous page disabled">
        <a>
            <i class="icon icon-chevron left"></i>
            <span class="name underline-middle-hover">Previous Page</span>
        </a>
    </div>
    <div class="pages">
        @foreach ($layout['pages'] as $pageNumber => $page)
            <a href="#" class="page-link {{$pageNumber === 0 ? 'active' : ''}}">{{$pageNumber + 1}}</a>
        @endforeach
    </div>
    <div class="next page {{count($layout['pages']) > 1 ? '' : 'disabled'}}">
        <a>
            <i class="icon icon-chevron right"></i>
            <span class="name underline-middle-hover">Next Page</span>
        </a>
    </div>
</section>