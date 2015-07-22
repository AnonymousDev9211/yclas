<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Panel_Category extends Auth_Crud {
	
	/**
	* @var $_index_fields ORM fields shown in index
	*/
	protected $_index_fields = array('name','order','price', 'id_category', 'id_category_parent');
	
	/**
	 * @var $_orm_model ORM model name
	 */
	protected $_orm_model = 'category';	


    /**
     * overwrites the default crud index
     * @param  string $view nothing since we don't use it
     * @return void      
     */
    public function action_index($view = NULL)
    {
        //HTTP::redirect(Route::url('oc-panel',array('controller'  => 'category','action'=>'dashboard')));  
        //template header
        $this->template->title  = __('Categories');

        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Categories')));
        $this->template->styles  = array('css/sortable.css' => 'screen', 
                                         '//cdn.jsdelivr.net/bootstrap.tagsinput/0.3.9/bootstrap-tagsinput.css' => 'screen');
        
        $this->template->scripts['footer'][] = 'js/jquery-sortable-min.js';
        $this->template->scripts['footer'][] = 'js/oc-panel/categories.js';
        $this->template->scripts['footer'][] = '//cdn.jsdelivr.net/bootstrap.tagsinput/0.3.9/bootstrap-tagsinput.min.js';

        $cats  = Model_Category::get_as_array();
        $order = Model_Category::get_multidimensional();

        $this->template->content = View::factory('oc-panel/pages/categories/index',array('cats' => $cats,'order'=>$order));
    }

    /**
     * CRUD controller: CREATE
     */
    public function action_create()
    {

        $this->template->title = __('New').' '.__($this->_orm_model);
        
        $form = new FormOrm($this->_orm_model);
            
        if ($this->request->post())
        {
            if ( $success = $form->submit() )
            {
                //category is different than himself, cant be his ow father!!!
                if ($form->object->id_category == $form->object->id_category_parent)
                {
                    Alert::set(Alert::INFO, __('You can not set as parent the same category'));
                    $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'create')));
                }

                //check if the parent is loaded/exists avoiding errors
                $parent_cat = new Model_Category($form->object->id_category_parent);
                if (!$parent_cat->loaded())
                {
                    Alert::set(Alert::INFO, __('You are assigning a parent category that does not exist'));
                    $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'create')));
                }

                $form->object->description = Kohana::$_POST_ORIG['formorm']['description'];
                
                try {
                    $form->object->save();
                } catch (Exception $e) {
                    throw HTTP_Exception::factory(500,$e->getMessage());  
                }

                $this->action_deep();
                Core::delete_cache();

                Alert::set(Alert::SUCCESS, __('Category created'));
            
                $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller())));
            }
            else 
            {
                Alert::set(Alert::ERROR, __('Check form for errors'));
            }
        }
    
        return $this->render('oc-panel/pages/categories/create', array('form' => $form));
    }

    /**
     * CRUD controller: UPDATE
     */
    public function action_update()
    {
        $this->template->title = __('Update').' '.__($this->_orm_model).' '.$this->request->param('id');
        $this->template->styles = array('css/sortable.css' => 'screen');
        $this->template->scripts['footer'][] = 'js/oc-panel/category_edit.js';
    
        $form = new FormOrm($this->_orm_model,$this->request->param('id'));
        $category = new Model_Category($this->request->param('id'));
        
        if ($this->request->post())
        {
            if ( $success = $form->submit() )
            {
                //category is different than himself, cant be his ow father!!!
                if ($form->object->id_category == $form->object->id_category_parent)
                {
                    Alert::set(Alert::INFO, __('You can not set as parent the same category'));
                    $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'update','id'=>$form->object->id_category)));
                }

                //check if the parent is loaded/exists avoiding errors
                $parent_cat = new Model_Category($form->object->id_category_parent);
                if (!$parent_cat->loaded())
                {
                    Alert::set(Alert::INFO, __('You are assigning a parent category that does not exist'));
                    $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'update','id'=>$form->object->id_category)));
                }

                $form->object->description = Kohana::$_POST_ORIG['formorm']['description'];
                
                try {
                    $form->object->save();
                } catch (Exception $e) {
                    throw HTTP_Exception::factory(500,$e->getMessage());  
                }

                $form->object->parent_deep =  $form->object->get_deep();

                try {
                    $form->object->save();
                } catch (Exception $e) {
                    throw HTTP_Exception::factory(500,$e->getMessage());  
                }
                
                $this->action_deep();
                Core::delete_cache();

                Alert::set(Alert::SUCCESS, __('Item updated'));
                $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller())));
            }
            else
            {
                Alert::set(Alert::ERROR, __('Check form for errors'));
            }
        }
    
        return $this->render('oc-panel/pages/categories/update', array('form' => $form, 'category' => $category));
    }


    /**
     * saves the category in a specific order and change the parent
     * @return void 
     */
    public function action_saveorder()
    {
        $this->auto_render = FALSE;
        $this->template = View::factory('js');

        $cat = new Model_Category(core::get('id_category'));

        //check if the parent is loaded/exists avoiding errors
        $parent_cat = new Model_Category(core::get('id_category_parent'));

        if ($cat->loaded() AND $parent_cat->loaded())
        {
            //saves the current category
            $cat->id_category_parent = $parent_cat->id_category;
            $cat->parent_deep        = core::get('deep');

            //saves the categories in the same parent the new orders
            $order = 0;
            foreach (core::get('brothers') as $id_cat) 
            {
                $id_cat = substr($id_cat,3);//removing the li_ to get the integer

                //not the main category so loading and saving
                if ($id_cat!=core::get('id_category'))
                {
                    $c = new Model_Category($id_cat);
                    $c->order = $order;
                    $c->save();
                }
                else
                {
                    //saves the main category
                    $cat->order  = $order;
                    $cat->save();
                }
                $order++;
            }

            //recalculating the deep of all the categories
            $this->action_deep();
            Core::delete_cache();
            $this->template->content = __('Saved');
        }
        else
            $this->template->content = __('Error');


    }

    /**
     * CRUD controller: DELETE
     */
    public function action_delete()
    {
        $this->auto_render = FALSE;

        $category = new Model_Category($this->request->param('id'));

        //update the elements related to that ad
        if ($category->loaded())
        {
            //check if the parent is loaded/exists avoiding errors, if doesnt exist to the root
            $parent_cat = new Model_Category($category->id_category_parent);
            if ($parent_cat->loaded())
                $id_category_parent = $category->id_category_parent;
            else
                $id_category_parent = 1;
            

            //update all the siblings this category has and set the category parent
            $query = DB::update('categories')
                        ->set(array('id_category_parent' => $id_category_parent))
                        ->where('id_category_parent','=',$category->id_category)
                        ->execute();

            //update all the ads this category has and set the category parent
            $query = DB::update('ads')
                        ->set(array('id_category' => $id_category_parent))
                        ->where('id_category','=',$category->id_category)
                        ->execute();
            
            //delete icon_delete
            $root = DOCROOT.'images/categories/'; //root folder
            if (is_dir($root))
            {
                @unlink($root.$category->seoname.'.png');
            
                // delete icon from Amazon S3
                if(core::config('image.aws_s3_active'))
                    $s3->deleteObject(core::config('image.aws_s3_bucket'), 'images/categories/'.$category->seoname.'.png');
            
                // update category info
                $category->has_image = 0;
                $category->last_modified = Date::unix2mysql();
                $category->save();            
            }

            try
            {
                $category->delete();
                $this->template->content = 'OK';

                //recalculating the deep of all the categories
                $this->action_deep();
                Core::delete_cache();
                Alert::set(Alert::SUCCESS, __('Category deleted'));
                
            }
            catch (Exception $e)
            {
                 Alert::set(Alert::ERROR, $e->getMessage());
            }
        }
        else
             Alert::set(Alert::ERROR, __('Category not deleted'));

        
        HTTP::redirect(Route::url('oc-panel',array('controller'  => 'category','action'=>'index')));  

    }

     /**
     * Creates multiple categories just with name
     * @return void      
     */
    public function action_multy_categories()
    {
        $this->auto_render = FALSE;

        //update the elements related to that ad
        if ($_POST)
        {
            // d($_POST);
            if(core::post('multy_categories') !== "")
            {
                $multy_cats = explode(',', core::post('multy_categories'));
                $obj_category = new Model_Category();

                $insert = DB::insert('categories', array('name', 'seoname', 'id_category_parent'));
                foreach ($multy_cats as $name)
                {
                    $insert = $insert->values(array($name,$obj_category->gen_seoname($name),1));
                }
                // Insert everything with one query.
                $insert->execute();

                Core::delete_cache();
            }
            else
                Alert::set(Alert::INFO, __('Select some categories first.'));
        }
        
        HTTP::redirect(Route::url('oc-panel',array('controller'  => 'category','action'=>'index'))); 
    }

    /**
     * recalculating the deep of all the categories
     * @return [type] [description]
     */
    public function action_deep()
    {
        Core::delete_cache();

        //getting all the cats as array
        $cats_arr = Model_Category::get_as_array();  

        $cats = new Model_Category();
        $cats = $cats->order_by('order','asc')->find_all()->cached()->as_array('id_category');
        foreach ($cats as $cat) 
        {
            $deep = 0;

            //getin the parent of this category
            $id_category_parent = $cats_arr[$cat->id_category]['id_category_parent'];

            //counting till we find the begining
            while ($id_category_parent != 1 AND $id_category_parent != 0 AND $deep<10) 
            {
                $id_category_parent = $cats_arr[$id_category_parent]['id_category_parent'];
                $deep++;
            }

            //saving the category only if different deep
            if ($cat->parent_deep != $deep)
            {
                $cat->parent_deep = $deep;
                $cat->save();
            }
            
        }

        //Alert::set(Alert::INFO, __('Success'));
        //HTTP::redirect(Route::url('oc-panel',array('controller'  => 'location','action'=>'index'))); 
    }

	public function action_icon()
	{
		//get icon
		$icon = $_FILES['category_icon']; //file post
		
		$category = new Model_Category($this->request->param('id'));
		
		if (core::config('image.aws_s3_active'))
        {
            require_once Kohana::find_file('vendor', 'amazon-s3-php-class/S3','php');
            $s3 = new S3(core::config('image.aws_access_key'), core::config('image.aws_secret_key'));
        }
        
		if (core::post('icon_delete') AND $category->delete_icon()==TRUE)
		{            
            Alert::set(Alert::SUCCESS, __('Icon deleted.'));
            $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'update','id'=>$category->id_category)));

        }// end of icon delete

        if ( 
            ! Upload::valid($icon) OR
            ! Upload::not_empty($icon) OR
            ! Upload::type($icon, explode(',',core::config('image.allowed_formats'))) OR
            ! Upload::size($icon, core::config('image.max_image_size').'M'))
        {
        	if ( Upload::not_empty($icon) && ! Upload::type($icon, explode(',',core::config('image.allowed_formats'))))
            {
                Alert::set(Alert::ALERT, $icon['name'].' '.sprintf(__('Is not valid format, please use one of this formats "%s"'),core::config('image.allowed_formats')));
				$this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'update','id'=>$category->id_category)));
            } 
            if( ! Upload::size($icon, core::config('image.max_image_size').'M'))
            {
                Alert::set(Alert::ALERT, $icon['name'].' '.sprintf(__('Is not of valid size. Size is limited to %s MB per image'),core::config('general.max_image_size')));
            $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'update','id'=>$category->id_category)));
            }
            Alert::set(Alert::ALERT, $icon['name'].' '.__('Image is not valid. Please try again.'));
            $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'update','id'=>$category->id_category)));
        }
        else
        {
            if ($icon != NULL) // sanity check 
            {   
                // saving/uploading img file to dir.
                $path = 'images/categories/';
                $root = DOCROOT.$path; //root folder
                $icon_name = $category->seoname.'.png';
                
                // if folder does not exist, try to make it
               	if ( ! file_exists($root) AND ! @mkdir($root, 0775, true)) { // mkdir not successful ?
                        Alert::set(Alert::ERROR, __('Image folder is missing and cannot be created with mkdir. Please correct to be able to upload images.'));
                        return; // exit function
                };
                
                // save file to root folder, file, name, dir
                if ($file = Upload::save($icon, $icon_name, $root))
                {
                    // put icon to Amazon S3
                    if (core::config('image.aws_s3_active'))
                        $s3->putObject($s3->inputFile($file), core::config('image.aws_s3_bucket'), $path.$icon_name, S3::ACL_PUBLIC_READ);
                    
                    // update category info
                    $category->has_image = 1;
                    $category->last_modified = Date::unix2mysql();
                    $category->save();
                    
                    Alert::set(Alert::SUCCESS, $icon['name'].' '.__('Icon is uploaded.'));
                }
                else
                    Alert::set(Alert::ERROR, $icon['name'].' '.__('Icon file could not been saved.'));
                    
                $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(),'action'=>'update','id'=>$category->id_category)));
            }
            
        }
    }
    
    /**
    * deletes all the categories
    * @return void 
    */
    public function action_delete_all()
    {
        if(core::post('confirmation'))
        {
            //delete categories icons
            $categories = new Model_Category();
            $categories = $categories->where('id_category','!=','1')->find_all();
            
            foreach ($categories as $category)
            {
                $root = DOCROOT.'images/categories/'; //root folder
                if (is_dir($root))
                {
                    @unlink($root.$category->seoname.'.png');
                    
                    // delete icon from Amazon S3
                    if(core::config('image.aws_s3_active'))
                        $s3->deleteObject(core::config('image.aws_s3_bucket'), 'images/categories/'.$category->seoname.'.png');
                }
            }
            
            //set home category to all the ads
            $query = DB::update('ads')
                        ->set(array('id_category' => '1'))
                        ->execute();
            
            //delete all categories
            $query = DB::delete('categories')
                        ->where('id_category','!=','1')
                        ->execute();
            
            Alert::set(Alert::SUCCESS, __('All categories were deleted.'));
            
        }
        else {
            Alert::set(Alert::ERROR, __('You did not confirmed your delete action.'));
        }
        
        HTTP::redirect(Route::url('oc-panel',array('controller'=>'category', 'action'=>'index')));
    }

    /**
    * creates a custom field for selected category
    * @return void 
    */
    public function action_create_customfield()
    {
        $category = new Model_Category($this->request->param('id'));
        
        if ($category->loaded() AND $_POST)
        {
            if ( count(Model_Field::get_all()) > 65 ) //upper bound for custom fields
            {
                Alert::set(Alert::ERROR,__('You have reached the maximum number of custom fields allowed.'));
                $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(), 'action'=>'update', 'id'=>$category->id_category)));
            }

            $name  = URL::title(Core::post('name'));
            $field = new Model_Field();

            try {

                $options = array(
                                'label'             => Core::post('label'),
                                'tooltip'           => Core::post('tooltip'),
                                'required'          => (Core::post('required')=='on') ? TRUE : FALSE,
                                'searchable'        => (Core::post('searchable')=='on') ? TRUE : FALSE,
                                'admin_privilege'   => (Core::post('admin_privilege')=='on') ? TRUE : FALSE,
                                'show_listing'      => (Core::post('show_listing')=='on') ? TRUE : FALSE,
                                );

                if ($field->create($name, Core::post('type'), Core::post('values'), array($category->id_category), $options))
                {
                    Core::delete_cache();
                    Alert::set(Alert::SUCCESS,sprintf(__('Field %s created'), $name));
                }
                else
                    Alert::set(Alert::ERROR,sprintf(__('Field %s already exists') ,$name));


            } catch (Exception $e) {
                throw HTTP_Exception::factory(500, $e->getMessage());
            }

        }

        $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(), 'action'=>'update', 'id'=>$category->id_category)));
    }

    /**
    * removes a custom field from selected category
    * @return void 
    */
    public function action_remove_customfield()
    {
        $category = new Model_Category($this->request->param('id'));

        //update the elements related to that ad
        if ($category->loaded() AND Core::get('cf'))
        {
            $name        = substr(Core::get('cf'), strlen('cf_'));
            $field       = new Model_Field();
            $field_data  = $field->get($name);
            
            $options     = array('label'           => $field_data['label'],
                                 'tooltip'         => $field_data['tooltip'],
                                 'required'        => $field_data['required'],
                                 'searchable'      => $field_data['searchable'],
                                 'admin_privilege' => $field_data['admin_privilege'],
                                 'show_listing'    => $field_data['show_listing'],
                                );
            
            // remove current category from custom field categories
            if ( is_array($field_data['categories']) AND ($key = array_search($category->id_category, $field_data['categories'])) !== FALSE )
                unset($field_data['categories'][$key]);
    
            try {
                // update custom field categories or delete custom field if no categories left
                if (count($field_data['categories']) > 0)
                {
                    if ($field->update($name, $field_data['values'], $field_data['categories'], $options))
                    {
                        Core::delete_cache();
                        Alert::set(Alert::SUCCESS,sprintf(__('Field %s removed'), $name));
                    }
                    else
                        Alert::set(Alert::ERROR,sprintf(__('Field %s cannot be removed'), $name));
                }
                else
                {
                    if ($field->delete($name))
                    {
                        Core::delete_cache();
                        Alert::set(Alert::SUCCESS,sprintf(__('Field %s deleted'), $name));
                    }
                    else 
                        Alert::set(Alert::ERROR,sprintf(__('Field %s does not exists'), $name));
                }

            } catch (Exception $e) {
                throw HTTP_Exception::factory(500,$e->getMessage());
            }
        }

        $this->redirect(Route::get($this->_route_name)->uri(array('controller'=> Request::current()->controller(), 'action'=>'update', 'id'=>$category->id_category)));
    }
}
