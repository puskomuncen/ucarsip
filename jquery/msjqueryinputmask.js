/**
 * Create jQuery Input Mask (for PHPMaker 2025)
 * @license (C) 2022-2024 Masino Sinaga.
 */
ew.jQueryInputMaskOptions={};ew.createjQueryInputMask=function(formid,id,options){if(id.includes("$rowindex$"))return;var $=jQuery,el=ew.getElement(id,formid),sv=ew.getElement("sv_"+id,formid),$input=$(sv||el),format="";options=Object.assign({},ew.jQueryInputMaskOptions,options);var the_number=options.number;var the_decimals=options.decimals;var the_dec_point=options.dec_point;var the_thousands_sep=options.thousands_sep;var args={id:id,form:formid,number:true,options:options};$((function(){$input.inputmask(args.options)}))};
