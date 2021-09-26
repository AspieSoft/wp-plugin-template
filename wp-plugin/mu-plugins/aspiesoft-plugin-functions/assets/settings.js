; (function($) {

  // set toast message options
  toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-bottom-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "5000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
  };

  random.setLiteMode(false);
  random.setClearInterval(10000);

  let optionsInfo = {};
  if(typeof AspieSoftAdminOptionsInfo === 'object') {
    optionsInfo = AspieSoftAdminOptionsInfo;
    if(typeof optionsInfo === 'string') {
      try {
        optionsInfo = JSON.parse(optionsInfo);
      } catch(e) {}
    }
  } else {
    $('#wpbody-content').append('<h2>Error: Options Info Not Found!</h2>');
    return;
  }

  let optionsList = {};
  if(typeof AspieSoftAdminOptionsList === 'object') {
    optionsList = AspieSoftAdminOptionsList;
    if(typeof optionsInfo === 'string') {
      try {
        optionsList = JSON.parse(optionsList);
      } catch(e) {}
    }
  } else {
    $('#wpbody-content').append('<h2>Error: Options List Not Found!</h2>');
    return;
  }


  const adminForm = $('#aspiesoft-admin-options');

  handleOptions(optionsList, adminForm);


  function handleOptions(options, form, index = 0) {
    let keys = Object.keys(options).sort((a, b) => {
      if(options[a].priority < options[b].priority) {
        return -1;
      } else if(options[a].priority > options[b].priority) {
        return 1;
      }
      return 0;
    });

    for(let i = 0; i < keys.length; i++) {
      handleOptType(keys[i], options[keys[i]], form, index);
    }
  }


  function handleOptType(name, data, form, index = 0) {
    switch(data.type) {
      case 'tab':
        handleOpt_tab(name, data, form, index);
        break;
      case 'heading':
      case 'break':
      case 'line':
        handleOpt_html(name, data, form, index);
        break;
      case 'text':
      case 'number':
        handleOpt_input(name, data, form, index);
        break;
      case 'list':
      case 'textarea':
        handleOpt_list(name, data, form, index);
        break;
      case 'check':
        handleOpt_check(name, data, form, index);
        break;
      case 'select':
        handleOpt_select(name, data, form, index);
        break;
      case 'radio':
        handleOpt_radio(name, data, form, index);
        break;
    }
  }


  function handleOpt_tab(name, data, form, index) {
    let tab = $(`.tab[index="${index}"]`, form);
    let isFirst = false;
    if(!tab.length) {
      isFirst = true;
      tab = $(`
        <div class="tab" index="${index}">
          <div class="tab_links"></div>
          <div class="tab_content"></div>
        </div>
      `).appendTo(form);
    }

    let input = $(`<input type="button" name="${name}">`).appendTo($('.tab_links', tab));
    input.val(data.label);
    const content = $('<div name="' + name + '"></div>').appendTo($('.tab_content', tab));

    if(isFirst) {
      input.addClass('selected');
      content.addClass('selected');
    }

    if(data.heading) {
      let heading = $('<h1 style="margin: 0 12px 40px -12px;">' + data.heading + '</h1>').appendTo(content);
      heading.text(data.heading);
    }

    content.css(parseCss(data.css));

    handleOptions(data.options, content, index + 1);
  }


  function handleOpt_html(name, data, form, index) {
    if(data.type === 'header') {
      let input = $(`<h${data.size}></h${data.size}>`).appendTo(form);
      input.text(data.text);
      input.css(parseCss(data.css));
    } else if(data.type === 'break') {
      for(let i = 0; i < data.size; i++) {
        $(`<br>`).appendTo(form);
        input.css(parseCss(data.css));
      }
    } else if(data.type === 'line') {
      for(let i = 0; i < data.size; i++) {
        $(`<hr>`).appendTo(form);
        input.css(parseCss(data.css));
      }
    }
  }


  function handleOpt_input(name, data, form, index) {
    if(typeof data.layout === 'string') {

      let value = getValue(data, 'value');
      let defValue = getValue(data, 'default');

      let i = 0;
      let used = [];
      let html = data.layout.replace(/%s%?|%([0-9])%?|%([0-9]+)%/g, (_, n1, n2) => {
        let randID = genRandomId('input');

        let n = n1 ?? n2;
        if(n && n !== '') {
          used.push(n);

          let elm = $('<div></div>');
          let label = $(`<label for="${randID}" class="input"></label>`).appendTo(elm);
          label.text(data.label);

          let input = $(`<input type="${data.type}" id="${randID}" name="${name}" class="aspiesoft-option">`).appendTo(elm);
          let val = getValue({...data, value: value[n], default: defValue[n]});
          input.val(val);
          input.attr('placeholder', defValue[n]);
          input.attr('origValue', val);
          input.attr('origValueN', val);
          input.attr('defValue', defValue[n]);
          input.attr('global', data.global ? 'true' : 'false');
          input.css(parseCss(data.css));

          return elm.html();
        }
        while(used.includes(i) && i < 10000) {
          i++;
        }

        let elm = $('<div></div>');
        let label = $(`<label for="${randID}" class="input"></label>`).appendTo(elm);
        label.text(data.label);

        let input = $(`<input type="${data.type}" id="${randID}" name="${name}" class="aspiesoft-option">`).appendTo(elm);
        let val = getValue({...data, value: value[i], default: defValue[i]});
        input.val(val);
        input.attr('placeholder', defValue[i]);
        input.attr('origValue', val);
        input.attr('origValueN', val);
        input.attr('defValue', defValue[i]);
        input.attr('global', data.global ? 'true' : 'false');
        input.css(parseCss(data.css));

        i++;
        return elm.html();
      });
      form.append(html + '<br>');
      return;
    }


    let randID = genRandomId('input');

    let label = $(`<label for="${randID}" class="input"></label>`).appendTo(form);
    label.text(data.label);
    form.append('<br>');

    let input = $(`<input type="${data.type}" id="${randID}" name="${name}">`).appendTo(form);
    let value = getValue(data);
    input.val(value);
    input.attr('placeholder', defValue);
    input.attr('origValue', value);
    input.attr('origValueN', value);
    input.attr('defValue', defValue);
    input.attr('global', data.global ? 'true' : 'false');
    input.css(parseCss(data.css));

    form.append(`<br>`);
  }

  function handleOpt_list(name, data, form, index) {
    let randID = genRandomId('list');

    let value = getValue(data);
    let defValue = getValue(data, data.default);
    let label = $(`<label for="${randID}" class="list"></label>`).appendTo(form);
    label.text(data.label);
    form.append('<br>');

    let input;
    if(data.type === 'list') {
      if(Array.isArray(value)) {value = value.join('\r\n');}
      if(Array.isArray(defValue)) {defValue = defValue.join('\r\n');}
      input = $(`<textarea id="${randID}" class="list aspiesoft-option" name="${name}"></textarea>`).appendTo(form);
    } else {
      input = $(`<textarea id="${randID}" name="${name}" class="aspiesoft-option"></textarea>`).appendTo(form);
    }
    input.val(value);
    input.attr('placeholder', defValue);
    input.attr('origValue', value);
    input.attr('origValueN', value);
    input.attr('defValue', defValue);
    input.attr('global', data.global ? 'true' : 'false');
    input.css(parseCss(data.css));

    form.append('<br>');
  }

  function handleOpt_check(name, data, form, index) {
    let randID = genRandomId('list');

    let value = getValue(data);
    let defValue = getValue(data, data.default);
    let input = $(`<input type="checkbox" id="${randID}" name="${name}" class="aspiesoft-option-check">`).appendTo(form);
    let label = $(`<label for="${randID}" class="check"></label><br>`).appendTo(form);
    label.text(data.label);
    form.append('<br>');

    input.attr('origValue', value ? 'true' : 'false');
    input.attr('origValueN', value ? 'true' : 'false');
    input.attr('defValue', defValue ? 'true' : 'false');
    input.attr('global', data.global ? 'true' : 'false');
    input.css(parseCss(data.css));

    if(value) {
      input.prop('checked', true);
    }
  }

  function handleOpt_select(name, data, form, index) {
    let randID = genRandomId('list');

    let value = getValue(data);
    let defValue = getValue(data, data.default);

    let label = $(`<label for="${randID}" class="select"></label>`).appendTo(form);
    label.text(data.label);

    let select = $(`<select id="${randID}" name="${name}" class="aspiesoft-option"></select>`).appendTo(form);
    select.attr('origValue', value);
    select.attr('origValueN', value);
    select.attr('defValue', defValue);
    select.attr('global', data.global ? 'true' : 'false');
    select.css(parseCss(data.css));

    handleOpts(data.options, select);

    function handleOpts(opts, group) {
      if(Array.isArray(opts)) {
        for(let i = 0; i < opts.length; i++) {
          if(typeof opts[i] === 'object' || Array.isArray(opts[i])) {
            let newGroup = $(`<optgroup></optgroup>`).appendTo(group);
            handleOpts(opts[i], newGroup);
          } else {
            let opt = $(`<option></option>`).appendTo(group);
            opt.val(opts[i]);
            opt.text(opts[i]);
            if(opts[i] === value) {
              opt.prop('selected', true);
            }
          }
        }
      } else if(typeof opts === 'object') {
        let keys = Object.keys(opts);
        for(let i = 0; i < keys.length; i++) {
          if(typeof opts[keys[i]] === 'object' || Array.isArray(opts[keys[i]])) {
            let newGroup = $(`<optgroup></optgroup>`).appendTo(group);
            newGroup.attr('label', keys[i]);
            handleOpts(opts[keys[i]], newGroup);
          } else {
            let opt = $(`<option></option>`).appendTo(group);
            opt.val(keys[i]);
            opt.text(opts[keys[i]]);
            if(keys[i] === value) {
              opt.prop('selected', true);
            }
          }
        }
      } else {
        let opt = $(`<option></option>`).appendTo(group);
        opt.val(opts);
        opt.text(opts);
        if(opts === value) {
          opt.prop('selected', true);
        }
      }
    }

    form.append('<br>');
  }

  function handleOpt_radio(name, data, form, index) {
    let randID = genRandomId('list');

    let value = getValue(data);
    let defValue = getValue(data, data.default);

    let label = $(`<label for="${randID}" class="radio"></label>`).appendTo(form);
    label.text(data.label);
    form.append('<br>');

    let select = $(`<div id="${randID}" name="${name}"></div>`).appendTo(form);
    select.attr('origValue', value);
    select.attr('origValueN', value);
    select.attr('defValue', defValue);
    select.attr('global', data.global ? 'true' : 'false');
    select.css(parseCss(data.css));

    handleOpts(data.options, select);

    function handleOpts(opts, group) {
      let randID = genRandomId('list');

      if(Array.isArray(opts)) {
        for(let i = 0; i < opts.length; i++) {
          if(typeof opts[i] === 'object' || Array.isArray(opts[i])) {
            let newGroup = $(`<div></div>`).appendTo(group);
            handleOpts(opts[i], newGroup);
          } else {
            let opt = $(`<input type="radio" id="${randID}" name="${name}" class="aspiesoft-option">`).appendTo(group);
            opt.val(opts[i]);
            let label = $(`<label for="${randID}"></label>`);
            label.text(opts[i]);
            if(opts[i] === value) {
              opt.prop('checked', true);
            }
          }
        }
      } else if(typeof opts === 'object') {
        let keys = Object.keys(opts);
        for(let i = 0; i < keys.length; i++) {
          if(typeof opts[keys[i]] === 'object' || Array.isArray(opts[keys[i]])) {
            let newGroupLabel = $(`<label for="${randID}"></label>`).appendTo(group);
            newGroupLabel.text(keys[i]);
            let newGroup = $(`<div id="${randID}"></div>`).appendTo(group);
            newGroup.attr('label', keys[i]);
            handleOpts(opts[keys[i]], newGroup);
          } else {
            let opt = $(`<input type="radio" id="${randID}" name="${name}" class="aspiesoft-option">`).appendTo(group);
            opt.val(keys[i]);
            let label = $(`<label for="${randID}"></label>`);
            label.text(opts[keys[i]]);
            if(keys[i] === value) {
              opt.prop('checked', true);
            }
          }
        }
      } else {
        let opt = $(`<input type="radio" id="${randID}" name="${name}" class="aspiesoft-option">`).appendTo(group);
        opt.val(opts);
        let label = $(`<label for="${randID}"></label>`);
        label.text(opts);
        if(opts === value) {
          opt.prop('checked', true);
        }
      }
    }

    group.append('<br>');
  }



  function getValue(data, strict = false) {
    let value = null;
    if(strict) {
      value = data[strict];
    } else {
      if(data.value != null && data.value !== '') {
        value = data.value;
      } else {
        value = data.default;
      }
    }

    if(data.type === 'check') {
      if(value === 'TRUE' || value === 'true' || value === true || value === '1' || value === 1) {
        return true;
      }
      return false;
    }

    if(typeof value === 'string' && ((value.startsWith('{') && value.endsWith('}')) || (value.startsWith('[') && value.endsWith(']')))) {
      try {
        value = JSON.parse(value);
      } catch(e) {}
    }

    if(data.type === 'number') {
      if(Array.isArray(value)) {
        for(let i = 0; i < value.length; i++) {
          value[i] = Number(value[i]);
        }
      }
      return Number(value);
    }

    return value;
  }

  function getBoolValue(value){
    if(value === 'TRUE' || value === 'true' || value === true || value === '1' || value === 1) {
      return true;
    } else if(value === 'FALSE' || value === 'false' || value === false || value === '0' || value === 0) {
      return false;
    }
    return null;
  }

  function parseCss(css) {
    if(!css || css === '') {return {};}

    if(Array.isArray(css)) {
      css = '{' + css.join(',') + '}';
    } else if(typeof css === 'object') {
      return css;
    }

    if(typeof css === 'string') {
      if(css.startsWith('[') && css.endsWith(']')) {
        css = css.replace(/^\[(.*)\]$/, '{$1}');
      } else if(!css.startsWith('{') || !css.endsWith('}')) {
        css = '{' + css + '}';
      }
      try {
        return JSON.parse(css.replace(/([{,;])\s*(.*?)\s*:/g, (_, start, key) => {
          if(start === ';') {start = ',';}
          if(key.match(/^"(\\[\\"]|[^"])*"$/)) {
            return start + key + ':';
          }
          key = key.replace(/([\\"])/g, '\\$1');
          return start + key + ':';
        }).replace(/:\s*(.*?)\s*([,;}])/g, (_, val, end) => {
          if(end === ';') {end = ',';}
          if(val.match(/^"(\\[\\"]|[^"])*"$/)) {
            return ':' + val + end;
          }
          val = val.replace(/([\\"])/g, '\\$1');
          return ':' + val + end;
        }));
      } catch(e) {}
    }

    return {};
  }

  function genRandomId(name) {
    return name + '_' + random(10000, 99999999);
  }


  // handle tabs
  $(document).on('click', '.tab_links input', function(e) {
    e.preventDefault();

    let par = $(this).parent();
    $('input', par).removeClass('selected');
    $(this).addClass('selected');
    let cont = $(this).parent().parent().children('.tab_content');
    $(cont).children('div').removeClass('selected');
    $(cont).children('div[name="' + $(this).attr('name') + '"]').addClass('selected');
  });


  // handle ajax and submit buttons
  const changeList = [];
  const undoList = [];
  let lastChange = 0;

  $(document).on('focusin', '.aspiesoft-option', function() {
    $(this).data('val', $(this).val());
  });

  $(document).on('focusin', '.aspiesoft-option-check', function() {
    $(this).data('val', $(this).prop('checked'));
  });

  let ignoreChange = false;
  $(document).on('change', '.aspiesoft-option, .aspiesoft-option-check', function() {
    if(ignoreChange){return;}
    let now = new Date().getTime();

    let value = $(this).val();
    if($(this).hasClass('aspiesoft-option-check')){
      value = $(this).prop('checked');
    }

    if(lastChange < now - 5000) {
      lastChange = now;
      let index = changeList.map(e => e.elm).lastIndexOf(this);
      if(index !== -1) {
        changeList[index].value = value;
        return;
      }
    }
    lastChange = now;
    changeList.push({elm: this, value: value, oldValue: $(this).data('val')});
  });


  $(document).on('keydown', function(e) {
    if(e.keyCode === 90 && e.ctrlKey && !e.shiftKey) { // undo
      e.preventDefault();
      ignoreChange = true;
      let lastChange = changeList.pop();
      if(Array.isArray(lastChange)){
        lastChange = [...lastChange];
        undoList.push(lastChange);
        for(let i = 0; i < lastChange.length; i++){
          $(lastChange[i].elm).val(lastChange[i].oldValue);
        }
        ignoreChange = false;
        return;
      }
      lastChange = {...lastChange};
      undoList.push(lastChange);
      $(lastChange.elm).val(lastChange.oldValue);
      ignoreChange = false;
    } else if(e.keyCode === 90 && e.ctrlKey && e.shiftKey) { // redo
      e.preventDefault();
      ignoreChange = true;
      let lastChange = undoList.pop();
      if(Array.isArray(lastChange)) {
        lastChange = [...lastChange];
        changeList.push(lastChange);
        for(let i = 0; i < lastChange.length; i++) {
          $(lastChange[i].elm).val(lastChange[i].value);
        }
        ignoreChange = false;
        return;
      }
      lastChange = {...lastChange};
      changeList.push(lastChange);
      $(lastChange.elm).val(lastChange.value);
      ignoreChange = false;
    } else if(e.keyCode === 83 && e.ctrlKey && !e.shiftKey) { // save
      e.preventDefault();
      saveOptions();
    } else if(e.keyCode === 83 && e.ctrlKey && e.shiftKey) { // save network
      e.preventDefault();
      saveOptions(true);
    } else if(e.keyCode === 82 && e.ctrlKey && !e.shiftKey) { // reset to orig value
      let elm = $(':focus');
      if(elm.hasClass('aspiesoft-option')){
        e.preventDefault();
        elm.val(elm.attr('origValue'));
      } else if(elm.hasClass('aspiesoft-option-check')) {
        e.preventDefault();
        elm.prop('checked', getBoolValue(elm.attr('origValue')));
      }
    } else if(e.keyCode === 82 && e.ctrlKey && e.shiftKey) { // reset to orig network value
      let elm = $(':focus');
      if(elm.hasClass('aspiesoft-option')) {
        e.preventDefault();
        elm.val(elm.attr('origValueN'));
      } else if(elm.hasClass('aspiesoft-option-check')) {
        e.preventDefault();
        elm.prop('checked', getBoolValue(elm.attr('origValueN')));
      }
    } else if((e.keyCode === 68 && e.ctrlKey && !e.shiftKey) || e.keyCode === 46) { // reset to default value
      let elm = $(':focus');
      if(elm.hasClass('aspiesoft-option')) {
        e.preventDefault();
        elm.val(elm.attr('defValue'));
      } else if(elm.hasClass('aspiesoft-option-check')) {
        e.preventDefault();
        elm.prop('checked', getBoolValue(elm.attr('defValue')));
      }
    }
  });


  $(document).on('click', '#aspiesoft-admin-options-default', function(e){
    e.preventDefault();

    ignoreChange = true;

    let valList = [];

    $('.aspiesoft-option').each(function(){
      let elm = $(this);
      let oldVal = elm.val();
      let val = elm.attr('defValue');
      if(val && val !== ''){
        elm.val(val);
        valList.push({elm: this, value: val, oldValue: oldVal});
      }
    });

    $('.aspiesoft-option-check').each(function() {
      let elm = $(this);
      let oldVal = elm.prop('checked');
      let val = getBoolValue(elm.attr('defValue'));
      if(val !== null) {
        elm.prop('checked', val);
        valList.push({elm: this, value: val, oldValue: oldVal});
      }
    });

    changeList.push(valList);

    ignoreChange = false;

    toastr.info('Reset All Settings To Default!');
  });

  $(document).on('click', '#aspiesoft-admin-options-save', function(e){
    e.preventDefault();
    saveOptions();
  });

  $(document).on('click', '#aspiesoft-admin-options-save-global', function(e) {
    e.preventDefault();
    saveOptions(true);
  });


  function saveOptions(network = false) {
    let sendChangeList = {};

    $('.aspiesoft-option, .aspiesoft-option-check').each(function() {
      let elm = $(this);
      let origValue = null;
      if(network){
        origValue = elm.attr('origValueN');
      }else{
        origValue = elm.attr('origValue');
      }
      let defValue = elm.attr('defValue');
      let value = elm.val();

      let global = getBoolValue(elm.attr('global'));

      if(elm.hasClass('aspiesoft-option-check')){
        value = elm.prop('checked');
        defValue = getBoolValue(defValue);
        origValue = getBoolValue(origValue);
      }

      // if value changed
      if(value !== origValue){
        if(elm.hasClass('aspiesoft-option-check')){
          if(value === defValue){
            // if set to default
            sendChangeList['OPTION_' + elm.attr('name')] = (global ? 'g' : '') + 'del';
          } else {
            // if set to new value
            sendChangeList['OPTION_' + elm.attr('name')] = (global ? 'g' : '') + 'set:' + (value ? 'true' : 'false');
          }
        }else{
          if(!value || value === '' || value === defValue){
            // if set to default
            sendChangeList['OPTION_' + elm.attr('name')] = (global ? 'g' : '') + 'del';
          }else{
            // if set to new value
            sendChangeList['OPTION_' + elm.attr('name')] = (global ? 'g' : '') + 'set:' + value;
          }
        }
      }
    });


    if(!Object.keys(sendChangeList).length){
      toastr.warning('Nothing Was Changed!');
      return;
    }


    $.ajax({
      url: window.location.href,
      type: 'POST',
      data: {
        ...sendChangeList,
        AspieSoft_Settings_Token: optionsInfo.settingsToken || $('input[name="AspieSoft_Settings_Token"]').val(),
        AspieSoft_Settings_Token_Key: optionsInfo.settingsTokenKey || $('input[name="AspieSoft_Settings_Token_key"]').val(),
        UpdateOptions: (network ? 'network' : 'local'),
      },
      success: function(res, _, xhr){
        try{
          if(xhr.status === 404 || xhr.status === '404' || res.includes('<error>404</error>')) {
            if(network) {
              toastr.error('Failed To Update Network Settings!', 'Error: 404 Request Not Found or Invalid');
            } else {
              toastr.error('Failed To Update Settings!', 'Error: 404 Request Not Found or Invalid');
            }
            return;
          } else if(xhr.status === 403 || xhr.status === '403' || res.includes('<error>403</error>')) {
            if(network) {
              toastr.error('Failed To Update Network Settings!', 'Error: 403 Session Token Invalid or Missing');
            } else {
              toastr.error('Failed To Update Settings!', 'Error: 403 Session Token Invalid or Missing');
            }
            return;
          } else if(xhr.status === 401 || xhr.status === '401' || res.includes('<error>401</error>')) {
            if(network) {
              toastr.error('Failed To Update Network Settings!', 'Error: 401 Session Expired');
            } else {
              toastr.error('Failed To Update Settings!', 'Error: 401 Session Expired');
            }
            return;
          } else if(xhr.status !== 200 && xhr.status !== '200' && xhr.status !== 204 && xhr.status !== '204'){
            if(network) {
              toastr.error('Failed To Update Network Settings!', 'Error: ' + xhr.status);
            } else {
              toastr.error('Failed To Update Settings!', 'Error: ' + xhr.status);
            }
            return;
          } else if(res.includes('<error>')) {
            if(network) {
              toastr.error('Failed To Update Network Settings!', 'Error: Failed To Get Error Code');
            } else {
              toastr.error('Failed To Update Settings!', 'Error: Failed To Get Error Code');
            }
            return;
          }
        }catch(e){
          toastr.error('Failed To Display Previous Error!');
          return;
        }

        if(network) {
          $('.aspiesoft-option, .aspiesoft-option-check').each(function(){
            if($(this).hasClass('aspiesoft-option-check')){
              $(this).attr('origValueN', $(this).prop('checked') ? 'true' : 'false');
            }else{
              $(this).attr('origValueN', $(this).val());
            }
          });
          setTimeout(function(){
            toastr.success('Updated Network Settings!');
          }, 500);
        } else {
          $('.aspiesoft-option, .aspiesoft-option-check').each(function() {
            if($(this).hasClass('aspiesoft-option-check')) {
              $(this).attr('origValue', $(this).prop('checked') ? 'true' : 'false');
            } else {
              $(this).attr('origValue', $(this).val());
            }
          });
          setTimeout(function() {
            toastr.success('Updated Settings!');
          }, 500);
        }
      },
      error: function(){
        if(network) {
          toastr.error('Failed To Update Network Settings!', 'Error: ' + jqXHR.status);
        }else{
          toastr.error('Failed To Update Settings!', 'Error: ' + jqXHR.status);
        }
      }
    });
  }


  $(window).on('beforeunload', function() {
    $.ajax({
      url: window.location.href,
      type: 'POST',
      data: {
        AspieSoft_Settings_Token: optionsInfo.settingsToken || $('input[name="AspieSoft_Settings_Token"]').val(),
        AspieSoft_Settings_Token_Key: optionsInfo.settingsTokenKey || $('input[name="AspieSoft_Settings_Token_key"]').val(),
        UpdateOptions: 'RemoveSession',
      }
    });
  });

})(jQuery);
