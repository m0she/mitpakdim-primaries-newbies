root = window.mit ?= {}

############### UTILITIES ##############

getShareLink = (weights) ->
    base = window.location.href.replace /#.*$/, ''
    party = root.global.party.id
    district = if root.global.district then root.global.district.id else 'x'
    fragment = "#{party}/#{district}/#{encode_weights(weights)}"
    base + '#' + fragment

parse_weights = (weights) ->
    if not _.isString weights
        return
    parsed = {}
    _.each weights.split('i'), (item) ->
        [key, value] = item.split('x')
        parsed[Number(key)] = Number(value)
    return parsed

encode_weights = (weights) ->
    ("#{key}x#{value}" for key,value of weights).join('i')

ga =
    event: (args...) ->
        _gaq.push ['_trackEvent'].concat args
    social: (args...) ->
        _gaq.push ['_trackSocial'].concat args

############### JQUERY UI EXTENSIONS ##############

$.widget "mit.agendaSlider", $.extend({}, $.ui.slider.prototype, {
    _create : ->
        @element.append '<div class="ui-slider-back"></div>'
        @element.append '<div class="ui-slider-mid-range"></div>'
        @element.append '<div class="ui-slider-minus-button"></div>'
        @element.append '<div class="ui-slider-plus-button"></div>'
        $.ui.slider::_create.apply @
    setCandidateMarker : (value) ->
        candidate_marker_classname = "ui-slider-candidate-marker"
        if not @element.find(".#{candidate_marker_classname}").length
            handle = @element.find(".ui-slider-handle")
            handle.before "<div class='#{candidate_marker_classname}'></div>"
        @element.find(".#{candidate_marker_classname}").css
            left : value + "%"
    _refreshValue : ->
        $.ui.slider::_refreshValue.apply @
        value = @value()
        range = @element.find ".ui-slider-mid-range"
        @element.removeClass "minus plus"
        if value < 0
            @element.addClass "minus"
            range.css
                left : (50 + value / 2) + "%"
                right : "50%"
        if value > 0
            @element.addClass "plus"
            range.css
                left : "50%"
                right : (50 - value / 2) + "%"
})

############### SYNC ##############

root.syncEx = (options_override) ->
    (method, model, options) ->
        Backbone.sync(method, model, _.extend({}, options, options_override))

root.JSONPCachableSync = (callback_name) ->
    collisionDict = {}
    collisionPrevention = ->
        callback = callback_name or 'cachable'
        callback_value = if _.isFunction callback then callback() else callback
        index = collisionDict[callback_value] or 0
        collisionDict[callback_value] = index + 1
        if index
            callback_value += "__#{index}"
        #console.log "jsonp callback: #{callback_value}"
        return callback_value

    root.syncEx
        cache: true
        dataType: 'jsonp'
        jsonpCallback: collisionPrevention

root.syncOptions =
    dataType: 'jsonp'

# Assume a repo has a key named objects with a list of objects identifiable by an id key
smartSync = (method, model, options) ->
    options = _.extend {}, root.syncOptions, model.syncOptions, options
    getLocalCopy = ->
        repo = options.repo
        repo = if _.isString(repo) then root[repo] else repo
        if method isnt 'read' or not repo
            return null
        if model instanceof Backbone.Collection
            return repo
        # Assume could only be a Model
        _.where(repo.objects, id: model.id)[0]

    if localCopy = _.clone getLocalCopy()
        promise = $.Deferred()
        _.defer ->
            if _.isFunction options.success
                options.success localCopy, null # xhr
            promise.resolve localCopy, null
        return promise
    return (options.sync or Backbone.sync)(method, model, options)

############### MODELS ##############

class root.MiscModel extends Backbone.Model
class root.Agenda extends Backbone.Model
    defaults:
        uservalue: 0

############### COLLECTIONS ##############

class root.JSONPCollection extends Backbone.Collection
    sync: smartSync
    initialize: ->
        super(arguments...)
    parse: (response) ->
        return response.objects

class root.AgendaList extends root.JSONPCollection
    model: root.Agenda
    url: "http://www.oknesset.org/api/v2/agenda/"
    syncOptions:
        disable_repo: window.mit.agenda

############### VIEWS ##############

class root.TemplateView extends Backbone.View
    template: ->
        _.template( @get_template() )(arguments...)
    digestData : (data) ->
        data
    render: =>
        @$el.html( @template(@digestData @model.toJSON()) )
        @

class root.ListViewItem extends root.TemplateView
    tagName: "div"
    get_template: ->
        "<a href='#'><%= name %></a>"
    events :
        click : "onClick"
    onClick : ->
        @trigger 'click', @model, @

class root.ListView extends root.TemplateView
    initialize: ->
        super(arguments...)
        @options.itemView ?= root.ListViewItem
        @options.autofetch ?= true
        if @options.collection
            @setCollection(@options.collection)

    setCollection: (collection) ->
        @collection = collection
        @collection.on "add", @addOne
        @collection.on "reset", @addAll
        if @options.autofetch
            @collection.fetch()
        else
            @addAll()

    addOne: (modelInstance) =>
        view = new @options.itemView({ model:modelInstance })
        view.on 'all', @itemEvent
        @$el.append view.render().$el

    addAll: =>
        @initEmptyView()
        @collection.each(@addOne)

    initEmptyView: =>
        @$el.empty()

    itemEvent: =>
        @trigger arguments...

class root.AgendaListView extends root.ListView
    el: '.agendas'
    options:
        collection: new root.AgendaList

        itemView: class extends root.ListViewItem
            className : "agenda_item"
            render : ->
                super()
                @.$('.slider').agendaSlider
                    min : -100
                    max : 100
                    value : @model.get "uservalue"
                    stop : @onStop
                @
            onStop : (event, ui) =>
                if ui.value <= 5 and ui.value >= -5
                    $(ui.handle).closest('.slider').agendaSlider "value", 0
                    ui.value = 0
                @model.set
                    uservalue : ui.value
            get_template: ->
                $("#agenda_template").html()

    reset: (weights) ->
        @collection.each (agenda, index) ->
            if _.isNumber(value = weights[agenda.id])
                agenda.set "uservalue", value
                @.$(".slider").eq(index).agendaSlider "value", value

    getWeights: ->
        weights = {}
        @collection.each (agenda) =>
            weights[agenda.id] = agenda.get("uservalue")
        weights

class root.WeightBankView extends root.TemplateView
    el: '.weight_bank'
    initialize: ->
        super(arguments...)
        @model.on 'change', @render
    get_template: ->
        $("#bank_template").html()

class root.AppView extends Backbone.View
    el: '#app_root'

    initialize: =>
        @bank = new Backbone.Model
            points_total: 500
            points_left: 500
        @bankView = new root.WeightBankView
            model: @bank
        @bankView.render()
        @agendaListView = new root.AgendaListView
        return

    events:
        'click input:button[value=Share]': (event) ->
            root.facebookShare getShareLink @agendaListView.getWeights()
        'click input:button#show_weights': (event) ->
            instructions = "\u05DC\u05D4\u05E2\u05EA\u05E7\u05D4\u0020\u05DC\u05D7\u05E5\u0020\u05E2\u05DC\u0020\u05E6\u05D9\u05E8\u05D5\u05E3\u0020\u05D4\u05DE\u05E7\u05E9\u05D9\u05DD\u000A\u0043\u0074\u0072\u006C\u002B\u0043"
            window.prompt instructions, encode_weights @agendaListView.getWeights()

############### INIT ##############

$ ->
    root.appView = new root.AppView
    return
