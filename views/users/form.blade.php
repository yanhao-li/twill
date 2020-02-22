@extends('twill::layouts.form', [
    'contentFieldsetLabel' => __('twill::lang.user-management.content-fieldset-label'),
    'editModalTitle' => __('twill::lang.user-management.edit-modal-title'),
    'reloadOnSuccess' => true
])

@php
    $isSuperAdmin = isset($item->role) ? $item->role === 'SUPERADMIN' : false;
@endphp

@section('contentFields')
    @formField('input', [
        'name' => 'email',
        'label' => __('twill::lang.user-management.email')
    ])

    @can('manage-users')
        @if(!$isSuperAdmin && ($item->id !== $currentUser->id))
            @formField('select', [
                'name' => "role",
                'label' => __('twill::lang.user-management.role'),
                'options' => $roleList,
                'placeholder' => 'Select a role'
            ])
        @endif
    @endcan

    @if(config('twill.enabled.users-image'))
        @formField('medias', [
            'name' => 'profile',
            'label' => 'Profile image'
        ])
    @endif
    @if(config('twill.enabled.users-description'))
        @formField('input', [
            'name' => 'title',
            'label' => 'Title',
            'maxlength' => 250
        ])
        @formField('input', [
            'name' => 'description',
            'rows' => 4,
            'type' => 'textarea',
            'label' => 'Description'
        ])
    @endif

    @if($with2faSettings ?? false)
        @formField('checkbox', [
            'name' => 'google_2fa_enabled',
            'label' => '2-factor authentication',
        ])

        @unless($item->google_2fa_enabled ?? false)
            @component('twill::partials.form.utils._connected_fields', [
                'fieldName' => 'google_2fa_enabled',
                'fieldValues' => true,
            ])
                <img style="display: block; margin-left: auto; margin-right: auto;" src="{{ $qrCode }}">
                <div class="f--regular f--note" style="margin: 20px 0;">Please scan this QR code with a Google Authenticator compatible application and enter your one time password below before submitting. See a list of compatible applications <a href="https://github.com/antonioribeiro/google2fa#google-authenticator-apps" target="_blank" rel="noopener">here</a>.</div>
                @formField('input', [
                    'name' => 'verify-code',
                    'label' => 'One time password',
                ])
            @endcomponent
        @else
            @component('twill::partials.form.utils._connected_fields', [
                'fieldName' => 'google_2fa_enabled',
                'fieldValues' => false,
            ])
                @formField('input', [
                    'name' => 'verify-code',
                    'label' => 'One time password',
                    'note' => 'Enter your one time password to disable the 2-factor authentication'

                ])
            @endcomponent
        @endunless
    @endif

    @formField('select', [
      'name' => 'language',
      'label' => 'Language',
      'placeholder' => 'Select a language',
      'default' => App::getLocale(),
      'options' => [
          [
              'value' => 'en',
              'label' => getLanguageNativeNameFromCode('en')
          ],
          [
              'value' => 'ru',
              'label' => getLanguageNativeNameFromCode('ru')
          ],
          [
              'value' => 'ja',
              'label' => getLanguageNativeNameFromCode('ja')
          ]
      ]
    ])
@stop

@push('vuexStore')
    window.STORE.publication.submitOptions = {
        draft: [
          {
            name: 'save',
            text: {!! "'" . __('twill::lang.user-management.update-disabled-user') . "'" !!}
          },
          {
            name: 'save-close',
            text: {!! "'" . __('twill::lang.user-management.update-disabled-and-close') . "'" !!}
          },
          {
            name: 'save-new',
            text: {!! "'" . __('twill::lang.user-management.update-disabled-user-and-create-new') . "'" !!}
          },
          {
            name: 'cancel',
            text: {!! "'" . __('twill::lang.user-management.cancel') . "'" !!}
          }
        ],
        live: [
          {
            name: 'publish',
            text: {!! "'" . __('twill::lang.user-management.enable-user') . "'" !!}
          },
          {
            name: 'publish-close',
            text: {!! "'" . __('twill::lang.user-management.enable-user-and-close') . "'" !!}
          },
          {
            name: 'publish-new',
            text: {!! "'" . __('twill::lang.user-management.enable-user-and-create-new') . "'" !!}
          },
          {
            name: 'cancel',
            text: {!! "'" . __('twill::lang.user-management.cancel') . "'" !!}
          }
        ],
        update: [
          {
            name: 'update',
            text: {!! "'" . __('twill::lang.user-management.update') . "'" !!}
          },
          {
            name: 'update-close',
            text: {!! "'" . __('twill::lang.user-management.update-and-close') . "'" !!}
          },
          {
            name: 'update-new',
            text: {!! "'" . __('twill::lang.user-management.update-and-create-new') . "'" !!}
          },
          {
            name: 'cancel',
            text: {!! "'" . __('twill::lang.user-management.cancel') . "'" !!}
          }
        ]
      }
    @if ($item->id == $currentUser->id)
        window.STORE.publication.withPublicationToggle = false
    @endif
@endpush
