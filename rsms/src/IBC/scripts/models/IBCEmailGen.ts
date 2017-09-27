namespace ibc {
    export class IBCEmailGen extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCEmails", "getIBCEmailGenById&id=", "saveIBCEmailGen");

        static contextMenuMacros = [{
            name: '[PI]',
            title: 'PI Name',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Protocol Title]',
            title: 'Protocol Title',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Protocol Number]',
            title: 'Protocol Number',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Protocol Approval Date]',
            title: 'Protocol Approval Date',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Expiration Date]',
            title: 'Expiration Date',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Reference Number]',
            title: 'Reference Number',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Review Assignment Name]',
            title: 'Review Assignment Name',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Review Assignment Due Date]',
            title: 'Review Assignment Due Date',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Meeting Date]',
            title: 'Meeting Date',
            fun: function () {
                alert('dig')
            }
        }, {
            name: '[Location]',
            title: 'Location',
            fun: function () {
                alert('dig')
            }
        }];

        Corpus: string;

        Subject: string;

        Title: string;

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