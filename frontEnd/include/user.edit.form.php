<? require_once basePath.'class/zebra/Zebra_Form.php' ?>

<?
// instantiate a Zebra_Form object
$form = new Zebra_Form('form');

$obj = $form->add('hidden', 'action', 'update');
$obj = $form->add('hidden', 'id', @$user->id);

# anme
$form->add('label', 'label_name', 'name', 'Name:');

$obj = $form->add('text', 'name', @$user->name);

$obj->set_rule(array(
    // error messages will be sent to a variable called "error", usable in custom templates
    'required'  =>  array('error', 'The name is required!'),
));

# email
$form->add('label', 'label_email', 'email', 'Email address:');

$obj = $form->add('text', 'email', @$user->email);

$obj->set_rule(array(

    'required'  => array('error', 'Email is required!'),
    'email'     => array('error', 'Email address seems to be invalid!')

));

# username
$form->add('label', 'label_username', 'username', 'Username:');

$obj = $form->add('text', 'username', @$user->username);

$obj->set_rule(array(
    'required'  =>  array('error', 'The username is required!'),
));

// attach a note to the email element
$form->add('note', 'note_email', 'email', 'Please enter a valid email address.', array('style'=>'width:200px'));

# "password"
$form->add('label', 'label_password', 'password', 'Choose a password:');

$obj = $form->add('text', 'password', @$user->password);

$obj->set_rule(array(
    'required'  => array('error', 'Password is required!'),
    #'length'    => array(2, 10, 'error', 'The password must have between 2 and 10 characters'),
));

$form->add('note', 'note_password', 'password', 'Password must be have between 2 and 10 characters.');

# category
$form->add('label', 'label_category', 'category', 'Category:');

$obj = $form->add('select', 'category[]', (array)@$user->json['category'], array('class' => 'chosen', 'style' => 'width: 300px;', 'multiple' => 'multiple', 'data-placeholder' => 'Select category filter'));

$obj->add_options($categories);

# page access
$form->add('label', 'label_pages', 'pages', 'Services access:');

$obj = $form->add('select', 'pages[]', (array)@$user->json['pages'], array('class' => 'chosen', 'style' => 'width: 300px;', 'multiple' => 'multiple', 'data-placeholder' => 'Select services filter'));

$obj->add_options($pages);

# is admin?
$form->add('label', 'label_is_admin', 'is_admin', 'Is Admin:');
$obj = $form->add('checkbox', 'is_admin', @$user->json['is_admin']);

# is active?
$form->add('label', 'label_is_active', 'is_active', 'Is active:');
$obj = $form->add('checkbox', 'is_active', @$user->is_active);


// "submit"
$form->add('submit', 'btnsubmit', 'Submit');

// if the form validates
if ($form->validate()) {}

// auto generate output, labels to the left of form elements
$form->render('*horizontal');