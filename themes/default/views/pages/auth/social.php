<?if (Theme::get('premium')==1):?>
    <?if (core::count($providers = Social::enabled_providers()) > 0 OR core::config('social.oauth2_enabled') == TRUE) :?>
        <ul class="list-inline social-providers">
            <?foreach ($providers as $key => $provider) :?>     
                <li>
                    <?if(strtolower($key) == 'live'):?>
                        <a class="zocial <?=strtolower($key) == 'live' ? 'windows' : ''?>" href="<?=Route::url('default',array('controller'=>'social','action'=>'login','id'=>strtolower($key)))?>">
                            <?=$key?>
                        </a>
                    <?elseif(strtolower($key) == 'google'):?>
                        <a href="<?=Route::url('default',array('controller'=>'social','action'=>'login','id'=>strtolower($key)))?>" style="height:31px; width:180px; display: inline-block; margin-top: -5px;" class="abcRioButton abcRioButtonLightBlue">
                            <div class="abcRioButtonContentWrapper">
                                <div class="abcRioButtonIcon" style="padding:6px">
                                    <div style="width:18px;height:18px;" class="abcRioButtonSvgImageWithFallback abcRioButtonIconImage abcRioButtonIconImage18">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 48 48" class="abcRioButtonSvg">
                                            <g>
                                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                                                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                                                <path fill="none" d="M0 0h48v48H0z"></path>
                                            </g>
                                        </svg>
                                    </div>
                                </div>
                                <span style="font-size:12px;line-height:29px;" class="abcRioButtonContents">
                                    <span id="not_signed_ineid4prr8k1y8"><?= _e('Sign in with Google') ?></span>
                                </span>
                            </div>
                        </a>
                        <style>
                            .abcRioButton {
                                -webkit-border-radius:1px;
                                border-radius:1px;
                                -webkit-box-shadow 0 2px 4px 0px rgba(0,0,0,.25);
                                box-shadow:0 2px 4px 0 rgba(0,0,0,.25);
                                -webkit-box-sizing:border-box;
                                box-sizing:border-box;
                                -webkit-transition:background-color .218s,border-color .218s,box-shadow .218s;
                                transition:background-color .218s,border-color .218s,box-shadow .218s;
                                -webkit-user-select:none;
                                -webkit-appearance:none;
                                background-color:#fff;
                                background-image:none;
                                color:#262626;
                                cursor:pointer;
                                outline:none;
                                overflow:hidden;
                                position:relative;
                                text-align:center;
                                vertical-align:middle;
                                white-space:nowrap;
                                width:auto
                            }
                            .abcRioButton:hover{
                                -webkit-box-shadow:0 0 3px 3px rgba(66,133,244,.3);
                                box-shadow:0 0 3px 3px rgba(66,133,244,.3)
                            }
                            .abcRioButtonLightBlue{
                                background-color:#fff;
                                color:#757575
                            }
                            .abcRioButtonLightBlue:hover{
                                background-color:#fff;
                                color:#757575;
                                text-decoration: none;
                            }
                            .abcRioButtonLightBlue:active{
                                background-color:#eee;
                                color:#6d6d6d
                            }
                            .abcRioButtonIcon{
                                float:left
                            }
                            .abcRioButtonBlue .abcRioButtonIcon{
                                background-color:#fff;
                                -webkit-border-radius:1px;
                                border-radius:1px
                            }
                            .abcRioButtonSvg{
                                display:block
                            }
                            .abcRioButtonContents{
                                font-family:Roboto,arial,sans-serif;
                                font-size:14px;
                                font-weight:500;
                                letter-spacing:.21px;
                                margin-left:6px;
                                margin-right:6px;
                                vertical-align:top
                            }
                            .abcRioButtonContentWrapper{
                                height:100%;
                                width:100%
                            }
                            .abcRioButtonBlue .abcRioButtonContentWrapper{
                                border:1px solid transparent
                            }
                            .abcRioButtonErrorState .abcRioButtonContentWrapper,.abcRioButtonWorkingState .abcRioButtonContentWrapper{
                                display:none
                            }
                        </style>
                    <?else:?>
                        <a class="zocial <?=strtolower($key)?>" href="<?=Route::url('default',array('controller'=>'social','action'=>'login','id'=>strtolower($key)))?>">
                            <?=$key?>
                        </a>
                    <?endif?>
                </li>
            <?endforeach?>
            <?if (core::config('social.oauth2_enabled') == TRUE):?>
                <li>
                    <a class="zocial secondary" href="<?=Route::url('default',array('controller'=>'social','action'=>'oauth','id'=>1))?>">
                        <?=__('OAuth')?>
                    </a>
                </li>
            <?endif?>
        </ul>
    <?endif?>
<?endif?>