## site using this bundle http://www.phphub.net/flux.rss

Installation using github
=========================

in your file deps add this lines

    [OSRssBundle]
        git=http://github.com/behcetenuygun/RssBundle.git
        target=bundles/OS/RssBundle

Execute

    php bin/vendors install

Add in your file app/AppKernel.php

    ...
    public function registerBundles() {
       $bundles = array(
            ...
            new OS\RssBundle\OSRssBundle(),
            ...
    }    

Add in your file app/autoload.php

    $loader->registerNamespaces(array(
        ...
        'OS' => __DIR__ . '/../vendor/bundles',
        ...
     

Configuration
=============
Include the lines below in to config.yml file

example
-------

    os_rss:
      title: Makaleler - Enuygun.com Bilgi
      description: İhtiyaç duyduğunuz konularla ilgili geniş bilgilere ulaşabileceğiniz, uzman yazarlarımızın bilgilerini bulabileceğiniz tüm makaleler bu bölümde.
      language: tr
      webMaster: bilgi@enuygun.com
      link: www.enuygun.com/bilgi
      generator: Enuygun.com RSS v1.0
      image: http://www.enuygun.com/img/v3/enuygun_logo.png
      icon: http://www.enuygun.com/img/favicon.ico
      copyright: Copyright 2008-2012 © Enuygun.com
      item:
        entity: EnuygunContentBundle:Post
        title: title
        description: content
        author: author
        pubDate: created
        guid: {route: content_post_view, params: {slug: slug}}
        link: {route: content_post_view, params: {slug: slug}}

The route for the example is:
_post:
  pattern: /{slug}/

The database table
  post(id, title, slug, content, created, updated)

if you have not slug field and you want to generate slug from title field use this configuration

link: {route: _post, params: {post_id: id, {field: title, class: App\CodeBundle\Inflector, method: slug}}}

add in your app/config/routing.yml
----------------------------------

    OSRssBundle:
      resource: "@OSRssBundle/Controller/"
      type: annotation
      prefix: /

Browse
http://yourserver/rss

