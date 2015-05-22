/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';
    
    var allContactIdsTxt = '' + $('#all-my-contacts-datas-box').data('contacts-id');
    allContactIdsTxt = allContactIdsTxt.trim();
    var allContactIds = allContactIdsTxt !== '' ?
        allContactIdsTxt.split(',') :
        [];

    $('#my-contacts-tool').on('click', '#contacts-configure-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_options_configure_form'),
            refreshPage,
            function() {}
        );
    });

    $('#my-contacts-tool').on('click', '#category-create-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_category_create_form'),
            addCategory,
            function() {}
        );
    });

    $('#my-contacts-tool').on('click', '.category-edit-btn', function () {
        var categoryId = $(this).data('category-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_category_edit_form', {'category': categoryId}),
            renameCategory,
            function() {}
        );
    });

    $('#my-contacts-tool').on('click', '.category-delete-btn', function () {
        var categoryId = $(this).data('category-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_contact_category_delete', {'category': categoryId}),
            removeCategory,
            categoryId,
            Translator.trans('delete_category_confirm_message', {}, 'platform'),
            Translator.trans('delete_category_confirm_title', {}, 'platform')
        );
    });

    $('#my-contacts-tool').on('click', '#add-contacts-btn', function () {
        var userPicker = new UserPicker();
        var settings = {
            multiple: true,
            picker_name: 'contacts_picker',
            picker_title: Translator.trans(
                'select_users_to_add_to_your_contacts',
                {},
                'platform'
            ),
            blacklist: allContactIds
        };
        userPicker.configure(settings, addContacts);
        userPicker.open();
    });
    
    $('#all-my-contacts-content-body').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var route = $(element).attr('href');

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#all-my-contacts-content-body').html(datas);
            }
        });
    });

    $('#all-my-contacts-content-body').on('change', '#max-select', function() {
        var max = $(this).val();

        $.ajax({
            url: Routing.generate('claro_contact_show_all_my_contacts', {'max': max}),
            type: 'GET',
            success: function (datas) {
                $('#all-my-contacts-content-body').html(datas);
            }
        });
    });
    
    $('#all-my-contacts-content-body').on('click', '.delete-contact', function () {
        var contactId = $(this).data('contact-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_contact_delete', {'contact': contactId}),
            removeContact,
            contactId,
            Translator.trans('delete_contact_confirm_message', {}, 'platform'),
            Translator.trans('delete_contact', {}, 'platform')
        );
    });
    
    $('#all-visible-users-content-body').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var route = $(element).attr('href');

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#all-visible-users-content-body').html(datas);
            }
        });
    });

    $('#all-visible-users-content-body').on('change', '#max-select', function() {
        var max = $(this).val();

        $.ajax({
            url: Routing.generate('claro_contact_show_all_visible_users', {'max': max}),
            type: 'GET',
            success: function (datas) {
                $('#all-visible-users-content-body').html(datas);
            }
        });
    });
    
    var refreshPage = function () {
        window.location.reload();
    };
    
    var addCategory = function (datas) {
        var id = datas['id'];
        var name = datas['name'];
      
        var categoryElement =
            '<div class="panel panel-default category-element" id="category-box-' +
                id + '" style="overflow: visible">' +
                
                '<div class="panel-heading">' +
                    '<h4 class="panel-title">' +
                        '<a data-toggle="collapse" href="#category-content-' +
                            id + '" id="category-title-' + id + '">' +
                            
                            name +
                        '</a>' +
                        '&nbsp;&nbsp;' +
                        '<span class="badge" id="category-badge-' + id + '">' +
                            '0' +
                        '</span>' +
                        '<span class="dropdown pull-right">' +
                            '<i class="fa fa-cogs pointer-hand" data-toggle="dropdown">' +
                            '</i>' +
                            '<ul class="dropdown-menu" role="menu">' +
                                '<li role="presentation" class="category-edit-btn" data-category-id="' +
                                    id + '">' +
                                    
                                    '<a role="menuitem" tabindex="-1" href="#">' +
                                        '<i class="fa fa-edit"></i>&nbsp;' +
                                        Translator.trans('rename', {}, 'platform') +
                                    '</a>' +
                                '</li>' +
                                '<li role="presentation" class="category-delete-btn" data-category-id="' +
                                    id + '">' +
                                    
                                    '<a role="menuitem" tabindex="-1" href="#">' +
                                        '<i class="fa fa-times-circle"></i>&nbsp;' +
                                        Translator.trans('delete', {}, 'platform') +
                                    '</a>'+
                                '</li>' +
                            '</ul>' +
                        '</span>' +
                    '</h4>' +
                '</div>' +
                '<div id="category-content-' + id + '" class="panel-collapse collapse">' +
                    '<div class="panel-body" id="category-content-body-' + id + '">' +
                        '<div class="alert alert-warning" id="category-empty-alert-'+ id + '">' +
                            Translator.trans('no_contact', {}, 'platform') +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        
        $('#category-list-box').append(categoryElement);
    };
    
    var renameCategory = function (datas) {
        var id = datas['id'];
        var name = datas['name'];
        $('#category-title-' + id).html(name);
    };
    
    var removeCategory = function (event, categoryId) {
        $('#category-box-' + categoryId).remove();
    };
    
    var addContacts = function (userIds) {
        var parameters = {};
        parameters.userIds = userIds;
        var route = Routing.generate('claro_contacts_add');
        route += '?' + $.param(parameters);
        
        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                window.location.reload();
            }
        });
    };
    
    var removeContact = function (event, contactId) {
        var index = allContactIds.indexOf('' + contactId);
        var nbContacts = parseInt($('#all-my-contacts-badge').html());
        nbContacts--;
        $('#all-my-contacts-badge').html(nbContacts);
        
        if (index > -1) {
            allContactIds.splice(index, 1);
        }
        $('.contact-row-' + contactId).remove();
    };
})();