(function ($) {
	var AMDM = {
		init: function () {
			this.cacheDOM();
			this.eventListener();
		},
		cacheDOM: function () {
			this.$table = $('.ascendoor-metadata-manager-table');
		},
		eventListener: function () {
			this.$table.on('click', '.meta-action a[data-delete]', this.delete.bind(this));
			this.$table.on('click', '.meta-action a[data-edit]', this.enableEdit.bind(this));
			this.$table.on('click', '.meta-value .edit-action > a', this.cancelOrUpdate.bind(this));
		},
		delete: function (e) {
			e.preventDefault();

			var $this = $(e.currentTarget),
				_delete = $this.data('delete'),
				$parent = $this.closest('tr'),
				protected = $parent.data('protected'),
				post = $parent.data('post'),
				term = $parent.data('term'),
				user = $parent.data('user'),
				comment = $parent.data('comment'),
				id = $parent.data('id');

			if (!confirm(1 === protected ? amdm.protectedMeta : amdm.confirmDelete)) {
				return;
			}

			var data = {
				id: id,
				"amdm-delete": _delete
			};

			if ('undefined' !== typeof post) {
				data['action'] = 'amdm-post-meta-delete';
				data['post'] = post;
			} else if ('undefined' !== typeof term) {
				data['action'] = 'amdm-term-meta-delete';
				data['term'] = term;
			} else if ('undefined' !== typeof user) {
				data['action'] = 'amdm-user-meta-delete';
				data['user'] = user;
			} else if ('undefined' !== typeof comment) {
				data['action'] = 'amdm-comment-meta-delete';
				data['comment'] = comment;
			}

			if ('undefined' === typeof data['action']) {
				return;
			}

			$parent.parent().css({
				opacity: 0.4,
				cursor: 'progress',
			}).find('.ascendoor-metadata-action').css({
				pointerEvents: 'none',
			});

			$.ajax({
				url: amdm.ajax,
				type: 'post',
				data: data
			}).done(function (response) {
				if ('0' === response) {
					alert(amdm.invalidRequest);
				} else if ('1' === response) {
					$parent.slideUp('slow', function () {
						setTimeout(function () {
							$parent.remove();
							if (confirm(amdm.doReload)) {
								window.location.reload();
							}
						}, 500);
					});
				} else {
					alert(response);
				}
			}).always(function () {
				$parent.parent().css({
					opacity: '',
					cursor: '',
				}).find('.ascendoor-metadata-action').css({
					pointerEvents: '',
				});
			});
		},
		enableEdit: function (e) {
			e.preventDefault();

			var $this = $(e.currentTarget),
				$textarea = $this.closest('tr').find('.meta-value textarea'),
				rows = 5,
				oldRows = '';

			if ('undefined' !== typeof $textarea.data('rows')) {
				oldRows = $textarea.data('rows');
			}

			if (oldRows && parseInt(oldRows) > rows) {
				rows = oldRows;
			}
			$textarea.attr('rows', rows).prop('disabled', false).next().show();
		},
		cancelOrUpdate: function (e) {
			e.preventDefault();

			var $this = $(e.currentTarget),
				$parent = $this.closest('tr'),
				protected = $parent.data('protected'),
				post = $parent.data('post'),
				term = $parent.data('term'),
				user = $parent.data('user'),
				comment = $parent.data('comment'),
				id = $parent.data('id'),
				$textarea = $this.parent().prev(),
				cancel = $this.data('cancel'),
				update = $this.data('update');

			if ('undefined' !== typeof cancel) {
				if ('undefined' !== $textarea.data('rows')) {
					$textarea.attr('rows', $textarea.data('rows'));
				} else {
					$textarea.attr('rows', '');
				}

				$this.parent().hide().prev().prop('disabled', true);
			} else if ('undefined' !== update) {
				if (!confirm(1 === protected ? amdm.protectedMetaUpdate : amdm.confirmUpdate)) {
					return;
				}

				var data = {
					id: id,
					value: $textarea.val(),
					"amdm-update": update
				};

				if ('undefined' !== typeof post) {
					data['action'] = 'amdm-post-meta-update';
					data['post'] = post;
				} else if ('undefined' !== typeof term) {
					data['action'] = 'amdm-term-meta-update';
					data['term'] = term;
				} else if ('undefined' !== typeof user) {
					data['action'] = 'amdm-user-meta-update';
					data['user'] = user;
				} else if ('undefined' !== typeof comment) {
					data['action'] = 'amdm-comment-meta-update';
					data['comment'] = comment;
				}

				if ('undefined' === typeof data['action']) {
					return;
				}

				$parent.find('.meta-value .edit-action').css({
					pointerEvents: 'none',
				});

				$parent.parent().css({
					opacity: 0.4,
					cursor: 'progress',
				}).find('.ascendoor-metadata-action').css({
					pointerEvents: 'none',
				});

				$.ajax({
					url: amdm.ajax,
					type: 'post',
					data: data
				}).done(function (response) {
					if ('0' === response) {
						alert(amdm.invalidRequest);
					} else if ('1' === response) {
						if (confirm(amdm.doReload)) {
							window.location.reload();
						}
					} else {
						alert(response);
					}
				}).always(function () {
					$parent.find('.meta-value .edit-action').css({
						pointerEvents: '',
					});

					$parent.parent().css({
						opacity: '',
						cursor: '',
					}).find('.ascendoor-metadata-action').css({
						pointerEvents: '',
					});

					if ('undefined' !== $textarea.data('rows')) {
						$textarea.attr('rows', $textarea.data('rows'));
					} else {
						$textarea.attr('rows', '');
					}

					$this.parent().hide().prev().prop('disabled', true);
				});
			}
		}
	};

	if ($('.ascendoor-metadata-manager-table').length) {
		AMDM.init();
	}
})(jQuery);