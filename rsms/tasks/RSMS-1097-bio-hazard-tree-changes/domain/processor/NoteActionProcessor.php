<?php

class NoteActionProcessor extends A_ActionProcessor {
    function validate( A_HazardChangeAction &$action ): ActionProcessorResult {
        return new ActionProcessorResult(true);
    }

    function perform( A_HazardChangeAction &$action ): ActionProcessorResult {
        return new ActionProcessorResult(true);
    }

    function verify( A_HazardChangeAction &$action ): bool {
        return true;
    }
}
?>
