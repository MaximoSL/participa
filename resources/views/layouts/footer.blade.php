<footer class='main-footer' style='z-index:100;position:relative'>
  <div class='list-info'>
    <div class='container'>
      <div class='row'>
        <div class='col-sm-4'>
          <h5>Enlaces</h5>
          <ul>
            <li>
              <a target="_self" href='http://www.gob.mx/accesibilidad'>Accesibilidad</a>
            </li>
            <li>
              <a target="_self" href='http://www.gob.mx/privacidad'>Política de privacidad</a>
            </li>
            <li>
              <a target="_self" href='http://www.gob.mx/terminos'>Términos y Condiciones</a>
            </li>
            <li>
              <a href='http://www.ordenjuridico.gob.mx' target='_blank'>Marco Jurídico</a>
            </li>
            <li>
              <a href='http://portaltransparencia.gob.mx' target='_blank'>Portal de Obligaciones de Transparencia</a>
            </li>
            <li>
              <a href='https://www.infomex.org.mx/gobiernofederal/home.action' target='_blank'>Sistema Infomex</a>
            </li>
            <li>
              <a href='http://inicio.ifai.org.mx/SitePages/ifai.aspx'>
                INAI
              </a>
            </li>
          </ul>
        </div>
        <div class='col-sm-4'>
          <h5>¿Qué es gob.mx?</h5>
          <p>Es el portal único de trámites, información y participación ciudadana.</p>
          <a target="_self" href='http://www.gob.mx/que-es-gobmx'>Leer más</a>
	  <ul>
	    <li>
	      <a target="_self" href='http://www.gob.mx/en/index'>English</a>
            </li>
            <li>
              <a target="_self" href='http://www.gob.mx/temas'>Temas</a>
            </li>
            <li>
              <a target="_self" href='http://reformas.gob.mx/'>Reformas</a>
            </li>
          </ul>
	</div>
        <div class='col-sm-4'>
          <h5>Contacto</h5>
          <p>
            Insurgentes Sur 1735, Col. Guadalupe Inn.
            <br>
            Delegación Álvaro Obregón
            <br>
            México, D.F. C.P. 01020
          </p>
          <p>gobmx@funcionpublica.gob.mx</p>
          <p><a target="_self" href="http://www.gob.mx/atencion">Atención Ciudadana</a></p>
	  <p><a target="_self" href="http://www.gob.mx/tramites/ficha/presentacion-de-quejas-y-denuncias-en-la-sfp/SFP54">Quejas y denuncias</a></p>
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-4' ng-controller="EmailSubscribeController">
          <form role="form" id="subscribe" novalidate>
            <label for="email" style='font-weight: 300;'>Mantente informado. Suscríbete.</label>
            <div class='form-group-icon'>
              <input class="form-control" id="subscriptionEmail" placeholder="Ingresa tu correo electrónico" type="email" ng-model="user" aria-label="Ingresa tu correo electrónico"/>
              <button class='blue-right btn' id='subscribeBtn' type='button' ng-click="subscribeEmail()" aria-label="Suscribir">
                <i class='icon-caret-right'></i>
              </button>
            </div>
            <div ng-show="successMessage" class="message-subscribe">
              Agradecemos tu registro dentro de gob.mx. De esta manera estarás informado sobre las principales acciones y decisiones del Gobierno de la República. <br><br>
              Gracias por tu interés y colaboración. Juntos construimos <a target="_self" href="/">gob.mx</a>
            </div>
          </form>
        </div>
        <div class='col-sm-4 col-sm-offset-4'>
          <h5>Síguenos en</h5>
          <ul class='list-inline'>
            <li>
              <a class='social-icon facebook' href='https://www.facebook.com/PresidenciaMX'>Facebook</a>
            </li>
            <li>
              <a class='social-icon twitter' href='https://twitter.com/PresidenciaMX'>Twitter</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class='container'>
    <div class='row'>
      <div class='col-sm-4'>
        <img alt="gob.mx" height="39" src="http://www.gob.mx/assets/gobmxlogo.svg" width="126" />
      </div>
      <div class='col-sm-4 col-sm-offset-4'>
        <img alt="gob.mx" height="70" src="http://www.gob.mx/assets/logo_mexico.svg" width="172" />
      </div>
    </div>
  </div>
</footer>
