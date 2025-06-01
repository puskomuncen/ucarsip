/*!
 * Detail Preview for PHPMaker v2025.0.0
 * Copyright (c) e.World Technology Limited. All rights reserved.
 */
(function ($, ew) {
  'use strict';

  ew.PREVIEW = true;
  ew.PREVIEW_TEMPLATE = "<div class=\"ew-nav ew-preview-nav\"><!-- .ew-preview-nav -->\n    <ul class=\"nav nav-" + ew.PREVIEW_NAV_STYLE + "\" role=\"tablist\"></ul>\n    <div class=\"tab-content\"><!-- .tab-content -->\n        <div class=\"tab-pane fade\" role=\"tabpanel\"></div>\n    </div><!-- /.tab-content -->\n</div><!-- /.ew-preview-nav -->";
  ew.PREVIEW_LOADING_HTML = ew.spinnerTemplate();
  ew.PREVIEW_MODAL_HTML = "<div id=\"ew-preview-dialog\" class=\"ew-preview-container " + ew.PREVIEW_MODAL_CLASS + "\" role=\"dialog\" aria-hidden=\"true\"><div class=\"modal-dialog modal-xl\"><div class=\"modal-content\"><div class=\"modal-body\"></div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">" + ew.language.phrase("CloseBtn") + "</button></div></div></div></div>";
  ew.PREVIEW_MODAL_OPTIONS = {};
  ew.PREVIEW_MODAL_DIALOG = null;
  ew.PREVIEW_OFFCANVAS_HTML = "<div id=\"ew-preview-offcanvas\" class=\"ew-preview-container offcanvas offcanvas-" + ew.PREVIEW_OFFCANVAS_PLACEMENT + "\" data-bs-scroll=\"true\" tabindex=\"-1\" aria-labelledby=\"ew-preview-offcanvas-label\"><div class=\"offcanvas-header\"><h5 id=\"ew-preview-offcanvas-label\">" + ew.language.phrase("Details") + "</h5><button type=\"button\" class=\"btn-close text-reset\" data-bs-dismiss=\"offcanvas\" aria-label=\"Close\"></button></div><div class=\"offcanvas-body\"></div></div>";
  ew.PREVIEW_OFFCANVAS_OPTIONS = {};
  ew.PREVIEW_OFFCANVAS_SIDEBAR = null;
  const $document = $(document);

  // Add preview row
  let addRowToTable = function (r) {
    let $r = $(r),
      $tb = $r.closest("tbody"),
      row;
    if (ew.PREVIEW_SINGLE_ROW) {
      $tb.find("tr.ew-table-preview-row").remove();
      $tb.find("tr[aria-expanded]").not(r).attr("aria-expanded", "false");
    }
    let colSpan = Array.from(r.cells).reduce((acc, cur) => acc + cur.colSpan, 0),
      $sr = $r.nextAll("tr[data-rowindex!=" + $r.data("rowindex") + "]").first();
    if ($sr.hasClass("ew-table-preview-row")) {
      // Preview row exists
      return $sr[0];
    } else if (row = $tb[0].insertRow($sr[0] ? $sr[0].sectionRowIndex : -1)) {
      // Insert a new row
      $(row).addClass("ew-preview-container ew-table-preview-row expandable-body");
      $(row.insertCell(0)).addClass("ew-table-last-col").prop("colSpan", colSpan);
    }
    return row;
  };

  /**
   * Show preview row
   * @param {Event} e - Event with the following property
   * @param {HTMLElement} e.currentTarget
   * @returns
   */
  let showPreviewRow = e => {
    let $el = $(e.currentTarget).closest("tr"),
      isRow = $el.is("tr[data-rowindex]"),
      $r = isRow ? $el : $el.closest("tr[data-rowindex]"),
      $content = $r.find("[class$=_preview] .ew-preview"),
      $tbl = $r.closest("table");
    if (!$r[0] || !$content[0]) return;
    if ($r.attr("aria-expanded") === "true") {
      let row = addRowToTable($r[0]),
        $cell = $(row.cells[0]),
        id = "target" + ew.random();
      $cell.empty(); // Note: do not chain
      $cell.append(ew.PREVIEW_TEMPLATE); // Append the contents
      $cell.children().slideUp(0);
      $cell.find(".nav-tabs, .nav-pills, .nav-underline").append($content.find(".nav-item").clone(true)); // Append tabs
      $cell.find(".tab-pane").attr("id", id);
      $cell.find("[data-bs-toggle='tab']").attr({
        "data-bs-target": "#" + id,
        "aria-controls": id
      }) // Setup tabs
      .first().tab("show"); // Show the first tab
      $cell.children().slideDown(500); // Match AdminLTE ExpandableTable
    }
    ew.setupTable($tbl[0], true);
    // ew.fixLayoutHeight();
  };

  // Setup preview popover
  let detailPopover = function (i, btn) {
    var _bootstrap$Tooltip$ge;
    if (bootstrap.Popover.getInstance(btn)) return;
    let $parent = $(btn.closest(".ew-list-option-body, .ew-multi-column-list-option-table, .ew-multi-column-list-option-card"));
    (_bootstrap$Tooltip$ge = bootstrap.Tooltip.getInstance(btn)) == null || _bootstrap$Tooltip$ge.dispose(); // Dispose tooltip, if any
    btn = btn.closest(ew.PREVIEW_SELECTOR);
    if (!btn) return;
    if (!btn.classList.contains("ew-preview-btn")) btn.classList.add("ew-preview-btn");
    let inst = new bootstrap.Popover(btn, {
      ...ew.popoverOptions,
      delay: ew.PREVIEW_POPOVER_DELAY,
      placement: ew.PREVIEW_POPOVER_PLACEMENT,
      container: document.getElementById("ew-tooltip"),
      content: ew.PREVIEW_LOADING_HTML,
      customClass: "ew-preview-container ew-preview-popover",
      trigger: ew.PREVIEW_POPOVER_TRIGGER == "click" ? "click" : "manual"
    });
    btn.addEventListener("show.bs.popover", () => {
      var _getActivePopover;
      return (_getActivePopover = getActivePopover()) == null ? void 0 : _getActivePopover.hide();
    });
    btn.addEventListener("shown.bs.popover", () => {
      let id = "target" + ew.random(),
        $tip = $(inst._getTipElement());
      $tip.find(".popover-body").empty().html(ew.PREVIEW_TEMPLATE); // Add the preview template
      $tip.find(".nav-tabs, .nav-pills, .nav-underline").append($parent.find(".nav-item").clone(true)); // Append tabs
      $tip.find(".tab-pane").attr("id", id);
      $tip.find("[data-bs-toggle='tab']").attr({
        "data-bs-target": "#" + id,
        "aria-controls": id
      }) // Setup tabs
      .first().tab("show"); // Show the first tab
    });
    if (ew.PREVIEW_POPOVER_TRIGGER != "click") {
      btn.addEventListener(ew.PREVIEW_POPOVER_TRIGGER, function () {
        var _bootstrap$Popover$ge;
        if (this.getAttribute("aria-describedby"))
          // Showing
          return;
        (_bootstrap$Popover$ge = bootstrap.Popover.getInstance(this)) == null || _bootstrap$Popover$ge.show();
      });
    }
  };

  // Setup preview modal
  let detailModal = function (i, btn) {
    var _bootstrap$Tooltip$ge2;
    (_bootstrap$Tooltip$ge2 = bootstrap.Tooltip.getInstance(btn)) == null || _bootstrap$Tooltip$ge2.dispose(); // Dispose tooltip, if any
    btn = btn.closest(ew.PREVIEW_SELECTOR);
    if (!btn) return;
    if (!btn.classList.contains("ew-preview-btn")) btn.classList.add("ew-preview-btn");
    btn.addEventListener("click", () => {
      ew.PREVIEW_MODAL_DIALOG.attr("data-parent-id", this.closest("[id^=el]").id).modal("hide"); // Find id="el<n>_tablename_preview"
      ew.PREVIEW_MODAL_DIALOG.modal("show");
    });
  };

  // Setup preview offcanvas
  let detailOffcanvas = function (i, btn) {
    var _bootstrap$Tooltip$ge3;
    (_bootstrap$Tooltip$ge3 = bootstrap.Tooltip.getInstance(btn)) == null || _bootstrap$Tooltip$ge3.dispose(); // Dispose tooltip, if any
    btn = btn.closest(ew.PREVIEW_SELECTOR);
    if (!btn) return;
    if (!btn.classList.contains("ew-preview-btn")) btn.classList.add("ew-preview-btn");
    btn.addEventListener("click", () => {
      ew.PREVIEW_OFFCANVAS_SIDEBAR.attr("data-parent-id", this.closest("[id^=el]").id).offcanvas("hide"); // Find id="el<n>_tablename_preview"
      ew.PREVIEW_OFFCANVAS_SIDEBAR.offcanvas("show");
    });
  };

  /**
   * Tab "show" event
   * @param {Event} e - Event with the following properties
   * @param {HTMLElement} e.currentTarget
   * @param {boolean} e.shiftKey
   * @param {boolean} e.ctrlKey
   * @returns
   */
  let showTab = function (e) {
    let el = e.currentTarget,
      target = el.dataset.bsTarget || el.closest(".tab-pane"); // Tab or Paging/Sorting links
    if (!target) return;
    let $el = $(el),
      $target = $(target),
      {
        url,
        page,
        table,
        sort,
        sortOrder,
        sortType
      } = el.dataset,
      params = new URLSearchParams(),
      data = $target.data(table) || {};
    if (url) {
      // New tab or paging link
      data.url = url;
      sort = data.sort;
      sortOrder = data.sortOrder;
      if (page) {
        // Paging link
        $el.tooltip("hide");
        data.page = page;
        $target.data(table, data).find(".card.ew-grid").append(ew.overlayTemplate());
      } else {
        page = data.page || 1;
        $target.data(table, data).empty().html(ew.PREVIEW_LOADING_HTML);
      }
    } else if (sort) {
      // Sorting link
      url = data.url;
      if (sort !== data.sort || sortOrder !== data.sortOrder)
        // Reset
        data.page = page = 1;else page = data.page;
      data.sort = sort;
      data.sortOrder = sortOrder;
      $target.data(table, data).find(".card.ew-grid").append(ew.overlayTemplate());
    }
    if ($.isNumber(page)) params.set(ew.TABLE_PAGE_NUMBER, page);
    if (sort) {
      params.set(ew.TABLE_SORT, sort);
      if (["ASC", "DESC", "NO"].includes(sortOrder)) params.set("sortorder", sortOrder);
      if (e.shiftKey && !e.ctrlKey) params.set("cmd", "resetsort");else if (sortType == 2 && e.ctrlKey) params.set("ctrl", "1");
    }
    ew.fetch(url + "&" + params.toString()).then(async response => {
      var _response$headers$get;
      if ((_response$headers$get = response.headers.get("Content-Type")) != null && _response$headers$get.includes("json")) {
        let result = await response.json(),
          error = ew.getError(result);
        if (error) {
          ew.alert(error);
          return;
        }
      } else {
        let data = await response.text();
        $target.empty().html(data); // Append the detail records
        let selector = ".ew-detail-btn-group[data-table='" + table + "'][data-url='" + url.replace(/&detailfilters=.+/, "") + "']",
          $btns = $target.closest(".ew-preview-nav").find(selector); // Detail buttons
        if (!$btns[0])
          // Maybe moved to body
          $btns = $("body").children(selector);
        if ($btns.is("div")) {
          // Buttons
          $target.append($btns.removeClass("d-none")); // Append the buttons
        } else if ($btns.is("ul")) {
          // Dropdown menu
          let $navitem = $el.closest(".nav-item").addClass("dropdown"),
            $dropbtn = $navitem.find(".dropdown-toggle").addClass("active").removeClass("d-none");
          if (!$dropbtn[0]) {
            $dropbtn = $el.clone();
            $dropbtn.addClass("dropdown-toggle") // Change nav link to dropdown button
            .attr({
              "data-bs-toggle": "dropdown",
              "data-bs-target": null,
              "role": "button"
            }); // Note: Remove data-bs-target attribute
            $navitem.prepend($dropbtn); // Note: Use .prepend()
            $el.on("hide.bs.tab", () => {
              $navitem.removeClass("dropdown").find($el).removeClass("d-none active");
              $navitem.find(".dropdown-toggle").addClass("d-none").removeClass("active show");
            });
          }
          $el.addClass("d-none");
        }
        $target.find(".ew-pager .btn:not(.disabled), .ew-table-header > th > div[data-sort]").data({
          "target": target,
          "table": table
        }).on("click", showTab); // Setup buttons for paging/sorting
        let $navlinks = $target.closest(".ew-preview-nav").find(".nav-link");
        $target.find(".ew-detail-count.d-none[data-table][data-count]").each(function () {
          $navlinks.filter("[data-table='" + this.dataset.table + "']").find(".ew-detail-count-badge").attr("data-count", this.dataset.count).html(this.innerHTML);
        });
        $document.trigger($.Event("preview.ew", {
          target: $target[0],
          $tabpane: $target
        }));
      }
    }).then(() => {
      var _getActivePopover2, _bootstrap$Modal$getI;
      (_getActivePopover2 = getActivePopover()) == null || _getActivePopover2.update(); // Update popover
      let modal = document.querySelector("#ew-preview-dialog");
      if (modal != null && modal.classList.contains("show")) (_bootstrap$Modal$getI = bootstrap.Modal.getInstance(modal)) == null || _bootstrap$Modal$getI.handleUpdate(); // Update modal
    });
  };

  /**
   * Tab "hide" event
   * @param {Event} e - Event with the following property
   * @param {HTMLElement} e.currentTarget
   * @returns
   */
  let hideTab = function (e) {
    // Dispose dropdowns inside the tab
    $(e.currentTarget.dataset.bsTarget).find(".dropdown-toggle[id]").dropdown("dispose");
    // Hide all other dropdown menus
    let container = e.currentTarget.closest(".popover, .modal, .nav");
    if (container) $(container).find(".dropdown-toggle").dropdown("hide");
  };

  /**
   * Get active popover
   * @returns {Popover}
   */
  let getActivePopover = () => {
    let popover = document.querySelector(".ew-preview-popover.show");
    if (!popover)
      // Popover closed
      return null;
    let btn = document.querySelector(".ew-preview-btn[aria-describedby='" + popover.id + "']");
    if (btn) return bootstrap.Popover.getInstance(btn);
    return null;
  };

  // Extend
  Object.assign(ew, {
    showPreviewRow,
    detailPopover,
    detailModal,
    detailOffcanvas,
    showTab,
    hideTab,
    getActivePopover
  });

  // Init
  loadjs.ready("wrapper", function () {
    // Popover
    document.addEventListener("click", evt => {
      var _getActivePopover3;
      if (!evt.target.closest(".ew-preview-popover"))
        // Outside popover
        (_getActivePopover3 = getActivePopover()) == null || _getActivePopover3.hide(); // Outside popover
    });
    // Modal
    if (!document.getElementById("ew-preview-dialog")) $("body").append(ew.PREVIEW_MODAL_HTML);
    ew.PREVIEW_MODAL_DIALOG = $("#ew-preview-dialog").modal(ew.PREVIEW_MODAL_OPTIONS);
    $("#ew-preview-dialog").on("show.bs.modal", function () {
      $(this).find(".modal-body").empty().html(ew.PREVIEW_TEMPLATE); // Add the preview template
    }).on("shown.bs.modal", function (e) {
      let $this = $(this),
        id = "target" + ew.random();
      $this.find(".nav-tabs, .nav-pills, .nav-underline").append($("#" + this.dataset.parentId).find(".nav-item").clone(true)); // Append tabs
      $this.find(".tab-pane").attr("id", id);
      $this.find("[data-bs-toggle='tab']").attr({
        "data-bs-target": "#" + id,
        "aria-controls": id
      }) // Setup tabs
      .first().tab("show"); // Show the first tab
    });
    // Offcanvas
    if (!document.getElementById("ew-preview-offcanvas")) $("body").append(ew.PREVIEW_OFFCANVAS_HTML);
    ew.PREVIEW_OFFCANVAS_SIDEBAR = $("#ew-preview-offcanvas").offcanvas(ew.PREVIEW_OFFCANVAS_OPTIONS);
    $("#ew-preview-offcanvas").on("show.bs.offcanvas", function () {
      $(this).find(".offcanvas-body").empty().html(ew.PREVIEW_TEMPLATE); // Add the preview template
    }).on("shown.bs.offcanvas", function () {
      let $this = $(this),
        id = "target" + ew.random();
      $this.find(".nav-tabs, .nav-pills, .nav-underline").append($("#" + this.dataset.parentId).find(".nav-item").clone(true)); // Append tabs
      $this.find(".tab-pane").attr("id", id);
      $this.find("[data-bs-toggle='tab']").attr({
        "data-bs-target": "#" + id,
        "aria-controls": id
      }) // Setup tabs
      .first().tab("show"); // Show the first tab
    });
  });

  // Init preview
  let initPreview = function (e) {
    var _e$target;
    let el = (_e$target = e == null ? void 0 : e.target) != null ? _e$target : document,
      $el = $(el),
      isNested = el !== document,
      previewType = ew.PREVIEW_TYPE;
    // Setup events and tab links
    if (isNested || previewType == "row") {
      // Preview rows
      $el.find("tr[data-rowindex][data-widget='expandable-table']").on("expand.lte.expandableTable", e => {
        var _Pace;
        return !((_Pace = Pace) != null && _Pace.running) || e.preventDefault();
      }) // Add "expand" handler to rows
      .on("expanded.lte.expandableTable", showPreviewRow); // Add "expanded" handler to rows
    } else if (previewType == "popover") {
      // Popover
      $el.find(".ew-preview-btn").each(detailPopover);
    } else if (previewType == "modal") {
      // Modal
      $el.find(".ew-preview-btn").each(detailModal);
    } else if (previewType == "offcanvas") {
      // Offcanvas
      $el.find(".ew-preview-btn").each(detailOffcanvas);
    }
    $("#ew-modal-dialog").off("success.ew.modal").on("success.ew.modal", (e, args) => {
      // Modal dialog success
      $(args == null ? void 0 : args.navItem).trigger("show.bs.tab"); // Reload the row
    });
    // Init tabs
    $el.find(".ew-preview [data-bs-toggle='tab']").on("show.bs.tab", showTab).on("hide.bs.tab", hideTab);
  };

  // Default preview handler
  let preview = function (e) {
    ew.lazyLoad(e); // Load images
    ew.initPanels(e.$tabpane[0]); // Init panels
    e.$tabpane.find("table.ew-table").each(ew.setupTable); // Setup the table
    ew.initTooltips(e); // Init tooltips
    ew.initLightboxes(e); // Init lightboxes
    ew.initIcons(e); // Init icons
    initPreview(e); // Init preview
  };

  // Add handlers
  $(initPreview);
  $(".ew-multi-column-grid").on("load.ew", initPreview); // Layout changed from "cards" to "table" or vice versa
  $document.on("refresh.ew", initPreview);
  $document.on("preview.ew", preview);

})(jQuery, ew);
