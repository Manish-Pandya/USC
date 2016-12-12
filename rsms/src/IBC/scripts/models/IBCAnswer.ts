﻿namespace ibc {
    export class IBCAnswer extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllAnswerss", "getAnswerById&id=", "saveAnswer");

        constructor() {
            super();
        }

        hasGetAllPermission(): boolean {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                super.hasGetAllPermission(_.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        }
    }
}