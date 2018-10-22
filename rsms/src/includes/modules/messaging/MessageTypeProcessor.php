<?php

/**
 * A MessageTypeProcessor is responsible for consuming
 * a Message and producing an array of message Details
 * to be used by ProcessQueuedMessagesTask in queueing
 * Emails.
 */
interface MessageTypeProcessor {

    /**
     * @returns an Array of message details.
     * An instance of details should be an array with
     * the following fields:
     *   Array recipients
     *   String from
     *   Array macromap
     */
    public function process(Message $message);
}
?>