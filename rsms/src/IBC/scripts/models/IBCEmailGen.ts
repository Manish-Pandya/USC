namespace ibc {
    export class IBCEmailGen extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCEmails", "getIBCEmailGenById&id=", "saveIBCEmailGen");

        static contextMenuHandler = function (data, event) {
            console.log(this.innerText + ' selected', data, event);
            tinymce.activeEditor.execCommand('mceInsertContent', false, this.innerText);
        }

        static contextMenuMacros = [{
            name: '[PI]',
            title: 'PI Name',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Protocol Title]',
            title: 'Protocol Title',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Protocol Number]',
            title: 'Protocol Number',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Protocol Approval Date]',
            title: 'Protocol Approval Date',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Expiration Date]',
            title: 'Expiration Date',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Reference Number]',
            title: 'Reference Number',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Review Assignment Name]',
            title: 'Review Assignment Name',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Review Assignment Due Date]',
            title: 'Review Assignment Due Date',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Meeting Date]',
            title: 'Meeting Date',
            fun: IBCEmailGen.contextMenuHandler
        }, {
            name: '[Location]',
            title: 'Location',
            fun: IBCEmailGen.contextMenuHandler
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