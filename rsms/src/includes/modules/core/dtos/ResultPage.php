<?php

class ResultPage {
    public $results;
    public $resultsCount;
    public $totalCount;
    public $pageNumber;
    public $pageSize;

    public function __construct($results, $total_results_count, $page, $recordsPerPage){
        $this->results = $results;
        $this->totalCount = $total_results_count;
        $this->pageNumber = $page;
        $this->pageSize = $recordsPerPage;

        $this->resultsCount = count($this->results);
    }

    public function getResults(){ return $this->results; }
    public function getResultsCount(){ return $this->resultsCount; }
    public function getTotalCount(){ return $this->totalCount; }
    public function getPageNumber(){ return $this->pageNumber; }
    public function getPageSize(){ return $this->pageSize; }
}

?>