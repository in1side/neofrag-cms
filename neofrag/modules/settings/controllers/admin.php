<?php if (!defined('NEOFRAG_CMS')) exit;
/**************************************************************************
Copyright © 2015 Michaël BILCOT & Jérémy VALENTIN

This file is part of NeoFrag.

NeoFrag is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

NeoFrag is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with NeoFrag. If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

class m_settings_c_admin extends Controller_Module
{
	public $administrable = FALSE;

	public function index()
	{
		$this	->title($this('configuration'))
				->subtitle($this('general_settings'))
				->icon('fa-cog');
		
		$modules = $pages = array();
		
		foreach ($this->addons->get_modules() as $module)
		{
			if ($module->is_administrable())
			{
				$modules[] = $module;
			}
		}
		
		array_natsort($modules, function($a){
			return $a->get_title();
		});
		
		foreach ($modules as $module)
		{
			$pages[$module->name] = $module->get_title();
			
			if ($module->name == 'pages')
			{
				foreach ($module->load->model()->get_pages() as $page)
				{
					if ($page['published'])
					{
						$pages['pages/'.$page['name']] = str_repeat('&nbsp;', 10).$page['title'];
					}
				}
			}
		}

		$this->form
				->add_rules(array(
					'name' => array(
						'label'  => $this('site_title'),
						'value'  => $this->config->nf_name,
						'rules'  => 'required'
					),
					'description' => array(
						'label'  => $this('site_description'),
						'value'  => $this->config->nf_description,
						'rules'  => 'required'
					),
					'contact' => array(
						'label'  => $this('contact_email'),
						'value'  => $this->config->nf_contact,
						'type'   => 'email',
						'rules'  => 'required'
					),
					'default_page' => array(
						'label'  => $this('default_page'),
						'values' => $pages,
						'value'  => $this->config->nf_default_page,
						'type'   => 'select',
						'rules'  => 'required'
					),
					'humans_txt' => array(
						'label'  => '<a href="http://humanstxt.org/">humans.txt</a>',
						'type'   => 'textarea',
						'value'  => $this->config->nf_humans_txt
					),
					'robots_txt' => array(
						'label'  => '<a href="http://www.robotstxt.org//">robots.txt</a>',
						'type'   => 'textarea',
						'value'  => $this->config->nf_robots_txt
					),
					'analytics' => array(
						'label'  => $this('code_analytics'),
						'type'   => 'textarea',
						'value'  => $this->config->nf_analytics
					),
					'debug' => array(
						'label'  => $this('debug_mode'),
						'type'   => 'radio',
						'value'  => $this->config->nf_debug,
						'values' => array($this('debug_disabled'), $this('debug_admins_only'), $this('debug_always'))
					)
				))
				->add_submit($this('save'))
				->display_required(FALSE);

		if ($this->form->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				if ($var == 'analytics')
				{
					$value = implode("\r\n", array_map('trim', explode("\r\n", trim(preg_replace('#&lt;script.*?&gt;(.*?)&lt;/script&gt;#is', '\1', $value)))));
				}

				$this->config('nf_'.$var, $value);
			}

			notify('Préférences générales sauvegardées avec succès');

			refresh();
		}

		return new Row(
			new Col(
				new Panel(array(
					'body'    => FALSE,
					'content' => $this->load->view('menu')
				)),
				'col-md-3'
			),
			new Col(
				new Panel(array(
					'title'   => $this('general_settings'),
					'icon'    => 'fa-cog',
					'content' => $this->form->display()
				)),
				'col-md-9'
			)
		);
	}

	public function registration()
	{
		$this	->title($this('configuration'))
				->subtitle('Gestions des inscriptions')
				->icon('fa-sign-in fa-rotate-90');

		$users = $this->db	->select('user_id', 'username')
							->from('nf_users')
							->where('deleted', FALSE)
							->order_by('username')
							->get();

		$list_users = array();

		foreach ($users as $user)
		{
			$list_users[$user['user_id']] = $user['username'];
		}

		array_natsort($list_users);

		$this->form
				->add_rules(array(
					array(
						'label'   => 'Inscription',
						'type'    => 'legend'
					),
					'registration_status' => array(
						'label'   => 'Statut',
						'type'    => 'radio',
						'value'   => $this->config->nf_registration_status,
						'values'  => array('Ouvertes', 'Fermées')
					),
					/*'registration_validation' => array(
						'label'   => 'Validation',
						'type'    => 'radio',
						'value'   => $this->config->nf_registration_validation,
						'values'  => array('Automatique', 'Confirmation par e-mail')
					),*/
					'registration_charte' => array(
						'label'   => 'Règlement',
						'value'   => $this->config->nf_registration_charte,
						'type'    => 'editor'
					),
					array(
						'label'   => 'Message de bienvenue',
						'type'    => 'legend'
					),
					'welcome' => array(
						'type'    => 'checkbox',
						'checked' => array('on' => $this->config->nf_welcome),
						'values'  => array('on' => 'Envoyer un message privé aux nouveaux membres')
					),
					'welcome_user_id' => array(
						'label'   => 'Auteur du message',
						'values'  => $list_users,
						'value'   => $this->config->nf_welcome_user_id,
						'type'    => 'select',
						'size'    => 'col-md-5'
					),
					'welcome_title' => array(
						'label'   => 'Titre du message',
						'value'   => $this->config->nf_welcome_title,
						'type'    => 'text'
					),
					'welcome_content' => array(
						'label'   => 'Message de bienvenue',
						'value'   => $this->config->nf_welcome_content,
						'type'    => 'editor'
					)
				))
				->add_submit($this('save'))
				->display_required(FALSE);

		if ($this->form->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				if ($var == 'welcome')
				{
					$value = in_array('on', $value);
				}

				$this->config('nf_'.$var, $value);
			}

			notify('Gestion des inscriptions sauvegardée avec succès');

			refresh();
		}

		return new Row(
			new Col(
				new Panel(array(
					'body'    => FALSE,
					'content' => $this->load->view('menu')
				)),
				'col-md-3'
			),
			new Col(
				new Panel(array(
					'title'   => 'Gestions des inscriptions',
					'icon'    => 'fa-sign-in fa-rotate-90',
					'content' => $this->form->display()
				)),
				'col-md-9'
			)
		);
	}

	public function team()
	{
		$this	->title($this('configuration'))
				->subtitle('Notre structure')
				->icon('fa-users');

		$this->form
				->add_rules(array(
					'team_name' => array(
						'label'       => 'Nom de l\'équipe',
						'value'       => $this->config->nf_team_name,
						'type'        => 'text'
					),
					'team_logo' => array(
						'label'       => 'Logo',
						'value'       => $this->config->nf_team_logo,
						'type'        => 'file',
						'upload'      => 'logos',
						'info'        => ' d\'image (max. '.(file_upload_max_size() / 1024 / 1024).' Mo)',
						'check'       => function($filename, $ext){
							if (!in_array($ext, array('gif', 'jpeg', 'jpg', 'png')))
							{
								return 'Veuiller choisir un fichier d\'image';
							}
						},
						'description' => 'Le logo pourra être affiché dans le widget type <b>header</b> <i>(en remplacement du titre et slogan)</i>.'
					),
					'team_type' => array(
						'label'       => 'Type de structure',
						'value'       => $this->config->nf_team_type,
						'type'        => 'text',
						'size'        => 'col-md-4',
						'description' => '<b>Exemple:</b> Association, entreprise, marque, etc...'
					),
					'team_creation' => array(
						'label'       => 'Date de création',
						'value'       => $this->config->nf_team_creation,
						'type'        => 'date',
						'size'        => 'col-md-4'
					),
					'team_biographie' => array(
						'label'       => 'Biographie',
						'value'       => $this->config->nf_team_biographie,
						'type'        => 'textarea'
					)
				))
				->add_submit($this('save'))
				->display_required(FALSE);

		if ($this->form->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				$this->config('nf_'.$var, $value);
			}

			notify('Informations sauvegardées avec succès');

			refresh();
		}

		return new Row(
			new Col(
				new Panel(array(
					'body'    => FALSE,
					'content' => $this->load->view('menu')
				)),
				'col-md-3'
			),
			new Col(
				new Panel(array(
					'title'   => 'Notre structure',
					'icon'    => 'fa-users',
					'content' => $this->form->display()
				)),
				'col-md-9'
			)
		);
	}

	public function socials()
	{
		$this	->title($this('configuration'))
				->subtitle('Réseaux sociaux')
				->icon('fa-globe');

		$this->form
				->add_rules(array(
					'social_facebook' => array(
						'label' => 'Facebook',
						'icon'  => 'fa-facebook',
						'value' => $this->config->nf_social_facebook,
						'type'  => 'url'
					),
					'social_twitter' => array(
						'label' => 'Twitter',
						'icon'  => 'fa-twitter',
						'value' => $this->config->nf_social_twitter,
						'type'  => 'url'
					),
					'social_google' => array(
						'label' => 'Google+',
						'icon'  => 'fa-google-plus',
						'value' => $this->config->nf_social_google,
						'type'  => 'url'
					),
					'social_steam' => array(
						'label' => 'Page Steam',
						'icon'  => 'fa-steam',
						'value' => $this->config->nf_social_steam,
						'type'  => 'url'
					),
					'social_twitch' => array(
						'label' => 'Twitch',
						'icon'  => 'fa-twitch',
						'value' => $this->config->nf_social_twitch,
						'type'  => 'url'
					),
					'social_dribble' => array(
						'label' => 'Dribbble',
						'icon'  => 'fa-dribbble',
						'value' => $this->config->nf_social_dribble,
						'type'  => 'url'
					),
					'social_behance' => array(
						'label' => 'Behance',
						'icon'  => 'fa-behance',
						'value' => $this->config->nf_social_behance,
						'type'  => 'url'
					),
					'social_deviantart' => array(
						'label' => 'DeviantArt',
						'icon'  => 'fa-deviantart',
						'value' => $this->config->nf_social_deviantart,
						'type'  => 'url'
					),
					'social_flickr' => array(
						'label' => 'Flickr',
						'icon'  => 'fa-flickr',
						'value' => $this->config->nf_social_flickr,
						'type'  => 'url'
					),
					'social_github' => array(
						'label' => 'Github',
						'icon'  => 'fa-github',
						'value' => $this->config->nf_social_github,
						'type'  => 'url'
					),
					'social_instagram' => array(
						'label' => 'Instagram',
						'icon'  => 'fa-instagram',
						'value' => $this->config->nf_social_instagram,
						'type'  => 'url'
					),
					'social_youtube' => array(
						'label' => 'Youtube',
						'icon'  => 'fa-youtube',
						'value' => $this->config->nf_social_youtube,
						'type'  => 'url'
					)
				))
				->add_submit($this('save'))
				->display_required(FALSE);

		if ($this->form->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				$this->config('nf_'.$var, $value);
			}

			notify('Réseaux sociaux sauvegardés avec succès');

			refresh();
		}

		return new Row(
			new Col(
				new Panel(array(
					'body'    => FALSE,
					'content' => $this->load->view('menu')
				)),
				'col-md-3'
			),
			new Col(
				new Panel(array(
					'title'   => 'Réseaux sociaux',
					'icon'    => 'fa-globe',
					'content' => $this->form->display()
				)),
				'col-md-9'
			)
		);
	}

	public function captcha()
	{
		$this	->title($this('configuration'))
				->subtitle('Sécurité anti-bots')
				->icon('fa-shield');

		$this->form
				->add_rules(array(
					'captcha_public_key' => array(
						'label' => 'Clé publique Google',
						'value' => $this->config->nf_captcha_public_key,
						'type'  => 'text'
					),
					'captcha_private_key' => array(
						'label' => 'Clé privée Google',
						'value' => $this->config->nf_captcha_private_key,
						'type'  => 'text'
					)
				))
				->add_submit($this('save'))
				->display_required(FALSE);

		if ($this->form->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				$this->config('nf_'.$var, $value);
			}

			notify('Configuration de Google reCAPTCHA sauvegardée avec succès');

			refresh();
		}

		return new Row(
			new Col(
				new Panel(array(
					'body'    => FALSE,
					'content' => $this->load->view('menu')
				)),
				'col-md-3'
			),
			new Col(
				new Panel(array(
					'title'   => 'Configuration de Google reCAPTCHA',
					'icon'    => 'fa-shield',
					'content' => '<div class="alert alert-info"><a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">https://www.google.com/recaptcha/intro/index.html</a></div>'.$this->form->display()
				)),
				'col-md-9'
			)
		);
	}

	public function email()
	{
		$this	->title($this('configuration'))
				->subtitle('Serveur e-mail')
				->icon('fa-envelope-o');

		$this->form
				->add_rules(array(
					'email_smtp' => array(
						'label'  => 'Serveur SMTP',
						'value'  => $this->config->nf_email_smtp,
						'type'   => 'text'
					),
					'email_username' => array(
						'label'  => 'Utilisateur',
						'value'  => $this->config->nf_email_username,
						'type'   => 'text',
						'size'   => 'col-md-5'
					),
					'email_password' => array(
						'label'  => 'Mot de passe',
						'value'  => $this->config->nf_email_password,
						'type'   => 'password',
						'size'   => 'col-md-5'
					),
					'email_secure' => array(
						'label'  => 'Sécurité',
						'type'   => 'radio',
						'value'  => $this->config->nf_email_secure,
						'values' => array('SSL', 'TLS')
					),
					'email_port' => array(
						'label'  => 'Port',
						'value'  => $this->config->nf_email_port,
						'type'   => 'number',
						'size'   => 'col-md-2'
					),
				))
				->add_submit($this('save'))
				->display_required(FALSE);

		if ($this->form->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				$this->config('nf_'.$var, $value);
			}

			notify('Configuration du serveur SMTP sauvegardée avec succès');

			refresh();
		}

		return new Row(
			new Col(
				new Panel(array(
					'body'    => FALSE,
					'content' => $this->load->view('menu')
				)),
				'col-md-3'
			),
			new Col(
				new Panel(array(
					'title'   => 'Serveur e-mail',
					'icon'    => 'fa-envelope-o',
					'content' => $this->form->display()
				)),
				'col-md-9'
			)
		);
	}

	public function maintenance()
	{
		$this	->title($this('maintenance'))
				->icon('fa-power-off')
				->css('maintenance')
				->js('maintenance');
				
		$form_opening = $this->form
			->add_rules(array(
				'opening' => array(
					'type'  => 'datetime',
					'value' => $this->config->nf_maintenance_opening
				)
			))
			->fast_mode()
			->add_submit($this('save'))
			->save();

		$form_maintenance = $this->form
			->add_rules(array(
				'title' => array(
					'label' => $this('title'),
					'type'  => 'text',
					'value' => $this->config->nf_maintenance_title
				),
				'content' => array(
					'label' => $this('content'),
					'type'  => 'editor',
					'value' => $this->config->nf_maintenance_content
				),
				'logo' => array(
					'label'  => $this('logo'),
					'value'  => $this->config->nf_maintenance_logo,
					'type'   => 'file',
					'upload' => 'maintenance',
					'info'   => $this('file_picture', file_upload_max_size() / 1024 / 1024),
					'check'  => function($filename, $ext){
						if (!in_array($ext, array('gif', 'jpeg', 'jpg', 'png')))
						{
							return i18n('select_image_file');
						}
					}
				),
				'background' => array(
					'label'  => $this('background'),
					'value'  => $this->config->nf_maintenance_background,
					'type'   => 'file',
					'upload' => 'maintenance',
					'info'   => $this('file_picture', file_upload_max_size() / 1024 / 1024),
					'check'  => function($filename, $ext){
						if (!in_array($ext, array('gif', 'jpeg', 'jpg', 'png')))
						{
							return i18n('select_image_file');
						}
					}
				),
				'repeat' => array(
					'label'  => $this('background_repeat'),
					'value'  => $this->config->nf_maintenance_background_repeat,
					'values' => array(
						'no-repeat' => $this('no'),
						'repeat-x'  => $this('horizontally'),
						'repeat-y'  => $this('vertically'),
						'repeat'    => $this('both')
					),
					'type'   => 'radio'
				),
				'positionX' => array(
					'label'  => $this('position'),
					'value'  => $this->config->nf_maintenance_background_position ? explode(' ', $this->config->nf_maintenance_background_position)[0] : '',
					'values' => array(
						'left'   => $this('left'),
						'center' => $this('center'),
						'right'  => $this('right')
					),
					'type'   => 'radio'
				),
				'positionY' => array(
					'value'  => $this->config->nf_maintenance_background_position ? explode(' ', $this->config->nf_maintenance_background_position)[1] : '',
					'values' => array(
						'top'    => $this('top'),
						'center' => $this('middle'),
						'bottom' => $this('bottom')
					),
					'type'   => 'radio'
				),
				'background_color' => array(
					'label' => $this('background_color'),
					'value' => $this->config->nf_maintenance_background_color,
					'type'  => 'colorpicker'
				),
				'text_color' => array(
					'label' => $this('text_color'),
					'value' => $this->config->nf_maintenance_text_color,
					'type'  => 'colorpicker'
				),
				'facebook' => array(
					'label' => 'Facebook',
					'icon'  => 'fa-facebook',
					'value' => $this->config->nf_maintenance_facebook,
					'type'  => 'url'
				),
				'twitter' => array(
					'label' => 'Twitter',
					'icon'  => 'fa-twitter',
					'value' => $this->config->nf_maintenance_twitter,
					'type'  => 'url'
				),
				'google' => array(
					'label' => 'Google+',
					'icon'  => 'fa-google-plus',
					'value' => $this->config->{'nf_maintenance_google-plus'},
					'type'  => 'url'
				),
				'steam' => array(
					'label' => 'Steam',
					'icon'  => 'fa-steam',
					'value' => $this->config->nf_maintenance_steam,
					'type'  => 'url'
				),
				'twitch' => array(
					'label' => 'Twitch',
					'icon'  => 'fa-twitch',
					'value' => $this->config->nf_maintenance_twitch,
					'type'  => 'url'
				)
			))
			->add_submit($this('save'))
			->save();
			
		if ($form_opening->is_valid($post))
		{
			$this->config('nf_maintenance_opening', $post['opening']);
			refresh();
		}
		else if ($form_maintenance->is_valid($post))
		{
			$this	->config('nf_maintenance_title',               $post['title'])
					->config('nf_maintenance_content',             $post['content'])
					->config('nf_maintenance_logo',                $post['logo'], 'int')
					->config('nf_maintenance_background',          $post['background'], 'int')
					->config('nf_maintenance_background_repeat',   $post['repeat'])
					->config('nf_maintenance_background_position', $post['positionX'].' '.$post['positionY'])
					->config('nf_maintenance_background_color',    $post['background_color'])
					->config('nf_maintenance_text_color',          $post['text_color'])
					->config('nf_maintenance_facebook',            $post['facebook'])
					->config('nf_maintenance_twitter',             $post['twitter'])
					->config('nf_maintenance_google-plus',         $post['google'])
					->config('nf_maintenance_steam',               $post['steam'])
					->config('nf_maintenance_twitch',              $post['twitch']);

			refresh();
		}

		return new Row(
			new Col(
				new Panel(array(
					'title'   => $this('website_status'),
					'icon'    => 'fa-power-off',
					'content' => $this->load->view('maintenance')
				)),
				new Panel(array(
					'title'   => $this('planned_opening'),
					'icon'    => 'fa-clock-o',
					'content' => $form_opening->display()
				))
				, 'col-md-3'
			),
			new Col(
				new Panel(array(
					'title'   => $this('customizing_maintenance_page'),
					'icon'    => 'fa-paint-brush',
					'content' => $form_maintenance->display()
				))
				, 'col-md-9'
			)
		);
	}
}

/*
NeoFrag Alpha 0.1.4.2
./neofrag/modules/settings/controllers/admin.php
*/