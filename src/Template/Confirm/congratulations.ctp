<div class="content_wrapper">
    <div class="page_header text-center bb_section">
        <div class="header_title">
            <h1>CONGRATULATIONS!</h1>
        </div>
        <div class="header_text">
            <p>You have been selected as <?php echo $winnerTitle ?> for <?php echo $dateWon;?>. You have won a <?php echo $sitePrize ?>. </p>
            <p>
                To confirm your prize, please complete the form below.
                <?php if ($siteCode == 'EDW'): ?>
                    You will receive a link to your gift card shortly.
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="page_content">
        <div class="row">

            <div class="col-md-3 col-sm-3 col-sm-push-9">
                <div class="steps_wrap">
                    <h5>3 EASY STEPS</h5>
                    <ul>
                        <li class="active">1</li>
                        <li>2</li>
                        <li>3</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-md-offset-3 col-sm-9 col-sm-pull-3">

                <div class="form_wrap">
                    <form id="WinnerForm" class="form-horizontal" method="post">
                        <?php echo $this->Form->input('id',array(
                            'id'=>'winnerID',
                            'type'=>'hidden',
                            'value'=> isset($winnerData['id']) ? base64_encode($winnerData['id']) : ''));
                        ?>
                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <label for="first-name">Name <span>*</span></label>
                                <?php
                                echo $this->Form->input('name_winner', array(
                                    'id' => 'first-name',
                                    'class' => 'form-control',
                                    'label' => false,
                                    // 'required'=> true,
                                    'data-vldtr'=>'required',
                                    'value' => (isset($winnerData['first_name'])) ? $winnerData['first_name'].' '.$winnerData['last_name']: '',
                                    'disabled'=> (isset($expire)) ? $expire : false,
                                    'readonly'=> true
                                ));
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <label for="winner-email">Email <span>*</span></label>
                                <?php
                                echo $this->Form->input('email_winner', array(
                                    'id' => 'winner-email',
                                    'class' => 'form-control',
                                    'label' => false,
                                    'data-vldtr'=>'required',
                                    'value' => (isset($winnerData['email'])) ? $winnerData['email'] : '',
                                    'disabled'=> (isset($expire)) ? $expire : false,
                                    'readonly'=> true
                                ));
                                ?>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <label for="address">Address <span>*</span></label>
                                <?php
                                echo $this->Form->input('address_given', array(
                                    'id' => 'address',
                                    'class' => 'form-control',
                                    'label' => false,
                                    'data-vldtr'=>'required',
                                    'disabled'=> (isset($expire)) ? $expire : false,
                                    'value'=>(isset($savedWinnerAddress))?$savedWinnerAddress:""
                                ));
                                ?>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <label for="city">City <span>*</span></label>
                                <?php
                                echo $this->Form->input('city_given', array(
                                    'id' => 'city',
                                    'class' => 'form-control',
                                    'label' => false,
                                    'data-vldtr'=>'required',
                                    'disabled'=> (isset($expire)) ? $expire : false,
                                    'value'=>(isset($savedWinnerCity))?$savedWinnerCity:""
                                ));
                                ?>

                            </div>
                        </div>

                        <div class="row multi_col_row">
                            <div class="col-md-10 col-md-offset-1">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <label for="state">State <span>*</span></label>
                                        <?php
                                        echo $this->Form->input('state_given', array(
                                            'id' => 'state',
                                            'class' => 'form-control',
                                            'label' => false,
                                            'type'=>'select',
                                            'data-vldtr'=>'required',
                                            'options' => $statesUS,
                                            'disabled'=> (isset($expire)) ? $expire : false,
                                            'default'=>(isset($savedWinnerState))?$savedWinnerState:""
                                        ));
                                        ?>
                                    </div>
                                    <div class="col-sm-8">
                                        <label for="zip-code">Zip Code <span>*</span></label>
                                        <?php
                                        echo $this->Form->input('zip_given', array(
                                            'id' => 'zip-code',
                                            'class' => 'form-control',
                                            'label' => false,
                                            'data-vldtr'=>'required',
                                            'disabled'=> (isset($expire)) ? $expire : false,
                                            'value'=>(isset($savedWinnerZip))?$savedWinnerZip:""
                                        ));
                                        ?>
                                    </div>
                                 </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <label for="telephone">Phone<span>*</span></label>
                                <?php
                                echo $this->Form->input('phone_given', array(
                                    'id' => 'telephone',
                                    'class' => 'form-control',
                                    'label' => false,
                                    'required'=> true,
                                    'placeholder'=>'(XXX)-XXX-XXXX',
                                    'disabled'=> (isset($expire)) ? $expire : false,
                                    'value'=>(isset($savedWinnerphone))?$savedWinnerphone:""
                                ));
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <?php if($expire == 0): ?>
                                    <?php
                                    echo $this->Form->input('Continue', array(
                                                'id' => 'confirmButton',
                                                'type'=>'button',
                                                'label'=>false,
                                                'class' => 'btn form-control btn_green',
                                                'value' => 'Continue'
                                            ));
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <div class="col-md-10 col-md-offset-1">
                                <div class="form_footer"><p>Your information is safe with us.<br>View our <a href=" //www.everydaywinenr.com/privacy_policy.php"  target="_blank" >Privacy Policy</a></p></div>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>
    </div>
</div>
