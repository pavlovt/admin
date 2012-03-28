<?

# get user details
# if no user is given get the current user
function get_user($user_id = 0) {
  $user_id = $_SESSION['userDetails']['id'];
  
  if ((int)$user_id) {
    require_once classPath.'class.user.php';
    $User = new User();
    return $User->find($user_id);
  }

  return false;
}

# get the categories, the user is subscribed to
# if no user is given get the current user categories
function get_user_category($cats = array(), $include_parrent = false, $user_id = 0) {
  require_once classPath.'class.category.php';
  $user = (object)get_user($user_id);
  $Category = new Category();

  if ($include_parrent)
    return $Category->get_selected_by_parent($user->json['category']);

  $user_cats = $Category->get_selected($user->json['category']);
  if (!empty($cats)) {
    $user_cats = array_intersect((array)$cats, $user_cats);

  }

  return $user_cats;
}

# get the category names
function get_category_names($cats) {
  require_once classPath.'class.category.php';
  $Category = new Category();
  
  $catn  =array();
  if ($Category->loadList())
    $catn = $Category->getIndexedColumn('name');

  $names = array();
  foreach ((array)$cats as $cat_id) {
    if (!empty($catn[$cat_id]))
      $names[] = $catn[$cat_id];
  }

  return $names;
}

# get the category names
function get_user_pages($page_ids) {
  $pages = get_pages();
  
  $names = array();
  foreach ((array)$page_ids as $page_id) {
    if (!empty($pages[$page_id]))
      $names[] = $pages[$page_id];
  }

  return $names;
}

function int_array($cats) {
  # if is single category id make it array
  $cats = (array)$cats;

  # are all cats integer?
  $new_cats = array();
  foreach ($cats as $cat) {
    $cat = (int)$cat;
    if (!$cat) continue;

    $new_cats[] = $cat;
  }

  return $new_cats;
}

function get_media() {
  return json_decode(followMedia, true);
}

function get_pages() {
  return json_decode(pages, true);
}

# return the current page url without the pagenum and filter var
function get_current_url($params) {
  $url = $_SERVER["REQUEST_URI"];
  $url = parse_url($url);

  unset($params->pagenum);

  $url['query'] = '?'.http_build_query((array)$params);
  $url = join('', $url);

  return $url;

}