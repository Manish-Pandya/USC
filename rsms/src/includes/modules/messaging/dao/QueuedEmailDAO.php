<?php

class QueuedEmailDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new QueuedEmail());
    }

    public function getAllUnsent(){
        $whereSentDateNull = new WhereClauseGroup(array(
            new WhereClause("sent_date", "IS", 'NULL')
        ));

        $unsent = $this->getAllWhere($whereSentDateNull);
        return $unsent;
    }

    public function getQueue( $paging, $sortColumn = NULL, $sortDescending = FALSE, $activeOnly = FALSE, $module = null, $template = null, $search = null ){

        $filter_predicates = array();
        $filter_binds = array();
        $filter_bind_fn = null;

        $where_clause = '';
        if( $module ?? $template ?? $search){
            if( $module != null ){
                $filter_predicates[] = "mq.module = :module";
                $filter_binds[':module'] = $module;
            }

            if( $template != null ){
                $filter_predicates[] = "eq.template_id = :template";
                $filter_binds[':template'] = $template;
            }

            if( $search != null ){
                $search = "%$search%";
                $filter_predicates[] = "(eq.body like :search OR eq.recipients like :search OR eq.cc_recipients like :search)";

                $filter_binds[':search'] = $search;
            }

            $filter_bind_fn = function( $stmt ) use ($filter_binds){
                foreach($filter_binds as $filter => $value){
                    $stmt->bindValue($filter, $value);
                }
            };

            $where_clause = 'WHERE ' . implode(' AND ', $filter_predicates);
        }

        $sql = "SELECT
            mq.key_id as message_id,
            mq.module as module,
            mq.message_type as message_type,
            mq.send_on as scheduled_date,
            mq.sent_date as queued_date,
            mq.context_descriptor as context_descriptor,
            template.title as template_name,
            eq.key_id as email_id,
            eq.template_id as template_id,
            eq.recipients as recipients,
            eq.cc_recipients as cc_recipients,
            eq.send_from as send_from,
            eq.subject as subject,
            eq.body as body,
            eq.sent_date as sent_date,
            CONCAT('m', mq.key_id, 'e', COALESCE(eq.key_id, '')) as queue_item_id
        FROM message_queue mq
        LEFT OUTER JOIN email_queue eq ON eq.message_id = mq.key_id
        LEFT OUTER JOIN message_template template ON template.key_id = eq.template_id
        $where_clause
        ORDER BY eq.date_created DESC, mq.date_created DESC";

        // TODO: Query PAGE as QueueItemDto
        return $this->queryPage( $paging, $sql,
            function($stmt){
                return $stmt->fetchAll(PDO::FETCH_CLASS, 'QueueItemDto');
            },
            $filter_bind_fn
        );
    }
}
?>