<?php
namespace Peanut;

class Pagination
{
    private $totalCount       = 0;
    private $totalPages       = 0;
    private $currentPage      = 0;
    private $recordsPerPage   = 10;
    private $pagesPerBlock    = 9;
    private $viewStartEnd     = false;
    private $urlPattern;

    public function __construct($totalCount, $currentPage, $recordsPerPage = 10, $pagesPerBlock = 9, $urlPattern = '', $viewStartEnd = false)
    {
        $this->totalCount     = $totalCount;
        $this->recordsPerPage = $recordsPerPage;
        $this->currentPage    = $currentPage;
        $this->urlPattern     = $urlPattern;
        $this->pagesPerBlock  = $pagesPerBlock;
        $this->viewStartEnd   = $viewStartEnd;

        $this->totalPages     = ($this->recordsPerPage == 0 ? 0 : (int) ceil($this->totalCount / $this->recordsPerPage));
        $this->nextPage       = $this->currentPage < $this->totalPages ? $this->currentPage + 1 : null;
        $this->prevPage       = $this->currentPage > 1 ? $this->currentPage - 1 : null;
    }

    public static function getHtml($totalCount, $currentPage, $recordsPerPage = 10, $pagesPerBlock = 9, $urlPattern = '', $viewStartEnd = false)
    {
        $pagination = new Pagination($totalCount, $currentPage, $recordsPerPage, $pagesPerBlock = 9, $urlPattern, $viewStartEnd);

        return $pagination->toHtml();
    }

    public static function get($totalCount, $currentPage, $recordsPerPage = 10, $pagesPerBlock = 9, $urlPattern = '', $viewStartEnd = false)
    {
        $pagination = new Pagination($totalCount, $currentPage, $recordsPerPage, $pagesPerBlock = 9, $urlPattern, $viewStartEnd);

        return $pagination->toArray();
    }

    public function getPages()
    {
        $pages = [];

        if ($this->totalPages <= $this->pagesPerBlock) {
            for ($i = 1; $i <= $this->totalPages; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
        } else {
            if (true === $this->viewStartEnd) {
                $pagesPerBlock = $this->pagesPerBlock - 2;
            } else {
                $pagesPerBlock = $this->pagesPerBlock;
            }
            $numAdjacents = (int)floor(($pagesPerBlock - 1) / 2);

            if ($this->currentPage + $numAdjacents > $this->totalPages) {
                $startPage = $this->totalPages - $pagesPerBlock + 1;// + 2;
            } else {
                $startPage = $this->currentPage - $numAdjacents;
            }
            if ($startPage < 1) {
                $startPage = 1;
            }
            $endPage = $startPage + $pagesPerBlock - 1;
            if ($endPage >= $this->totalPages) {
                $endPage = $this->totalPages;
            }

            if (true === $this->viewStartEnd && $startPage > 2) {
                $pages[] = $this->createPage(1, $this->currentPage == 1);
                $pages[] = $this->createEllipsisPage();
            }
            for ($i = $startPage; $i <= $endPage; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
            if (true === $this->viewStartEnd && $endPage < $this->totalPages - 1) {
                $pages[] = $this->createEllipsisPage();
                $pages[] = $this->createPage($this->totalPages, $this->currentPage == $this->totalPages);
            }
        }

        return $pages;
    }

    public function toArray()
    {
        return [
            'totalPages'  => $this->totalPages,
            'currentPage' => $this->currentPage,
            'prevUrl'     => $this->getPageUrl($this->prevPage),
            'pages'       => $this->getPages(),
            'nextUrl'     => $this->getPageUrl($this->nextPage),
        ];
    }

    public function toHtml()
    {
        $pagination = $this->toArray();

        $html = '<ul class="pagination">';
        if ($pagination['prevUrl']) {
            $html .= '<li><a href="'.$pagination['prevUrl'].'">&laquo;</a></li>';//Previous
        } else {
            $html .= '<li class="disabled"><a>&laquo;</a></li>';
        }
        foreach ($pagination['pages'] as $page) {
            if ($page['url']) {
                $html .= '<li'.($page['isCurrent'] ? ' class="active"' : '').'><a href="'.$page['url'].'">'.$page['num'].'</a></li>';
            } else {
                $html .= '<li class="disabled"><span>'.$page['num'].'</span></li>';
            }
        }
        if ($pagination['nextUrl']) {
            $html .= '<li><a href="'.$pagination['nextUrl'].'">&raquo;</a></li>';//Next
        } else {
            $html .= '<li class="disabled"><a>&raquo;</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function getPageUrl($pageNum = null)
    {
        if (!$pageNum) {
            return null;
        }

        return str_replace('{=page}', $pageNum, $this->urlPattern);
    }

    private function createPage($pageNum, $isCurrent = false)
    {
        return [
            'num'       => $pageNum,
            'url'       => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        ];
    }

    private function createEllipsisPage()
    {
        return [
            'num'       => '...',
            'url'       => null,
            'isCurrent' => false,
        ];
    }
}

/*

$totalCount     = 1232;
$recordsPerPage = 50;
$pagesPerBlock  = 9;
$currentPage    = $request->getQuery('page');
$urlPattern     = '/attraction/?page={=page}';

// html
$pagingHtml  = pagination::getHtml($totalCount, $currentPage, $recordsPerPage, $pagesPerBlock, $urlPattern);

view::assign([
    'pagingHtml'  => $pagingHtml,
]);
*/

/*
// array, define
$paging = pagination::get($totalCount, $currentPage, $recordsPerPage, $pagesPerBlock, $urlPattern, true);

view::assign([
    'paging'  => $paging,
]);

view::define([
    'pagination' => 'theme/service/normal/pagination.phtml',
]);
*/

// theme/service/normal/pagination.phtml
/*

<nav style='text-align:center'>

    <ul class="pagination">
        {{?pagination.prevUrl}}
            <li><a href="{{=pagination.prevUrl}}">&laquo;</a></li>
        {{:}}
            <li class='disabled'><a>&laquo;</a></li>
        {{/}}

        {{@page =  pagination.pages}}
            {{?page.url}}
                <li {{?page.isCurrent}}class="active"{{/}}>
                    <a href="{{=page.url}}">{{=page.num}}</a>
                </li>
            {{:}}
                <li class="disabled"><span>{{=page.num}}</span></li>
            {{/}}
        {{/}}

        {{?pagination.nextUrl}}
            <li><a href="{{=pagination.nextUrl}}">&raquo;</a></li>
        {{:}}
            <li class='disabled'><a>&raquo;</a></li>
        {{/}}
    </ul>

</nav>

*/
