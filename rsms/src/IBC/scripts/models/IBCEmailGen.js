var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var ibc;
(function (ibc) {
    var IBCEmailGen = /** @class */ (function (_super) {
        __extends(IBCEmailGen, _super);
        function IBCEmailGen() {
            return _super.call(this) || this;
        }
        IBCEmailGen.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        IBCEmailGen.urlMapping = new UrlMapping("getAllIBCEmails", "getIBCEmailGenById&id=", "saveIBCEmailGen");
        IBCEmailGen.contextMenuHandler = function (data, event) {
            console.log(this.innerText + ' selected', data, event);
            tinymce.activeEditor.execCommand('mceInsertContent', false, this.innerText);
        };
        IBCEmailGen.contextMenuMacros = [{
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
        return IBCEmailGen;
    }(FluxCompositerBase));
    ibc.IBCEmailGen = IBCEmailGen;
})(ibc || (ibc = {}));
