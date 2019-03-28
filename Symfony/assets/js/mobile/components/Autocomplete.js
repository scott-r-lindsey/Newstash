
import React from 'react';
import { withStyles } from '@material-ui/core/styles';
import { withRouter } from 'react-router-dom'
import PropTypes from 'prop-types';

import deburr from 'lodash/deburr';

import Autosuggest from 'react-autosuggest';
import match from 'autosuggest-highlight/match';
import parse from 'autosuggest-highlight/parse';

import MenuItem from '@material-ui/core/MenuItem';
import Paper from '@material-ui/core/Paper';
import TextField from '@material-ui/core/TextField';

const api = '/search/json/typeahead';
const default_search = '/search/books?query='

const styles = theme => ({
  root: {
    flexGrow: 1,
  },
  container: {
    position: 'relative',
  },
  suggestionsContainerOpen: {
    position: 'absolute',
    zIndex: 1,
    marginTop: theme.spacing.unit,
    left: 0,
    right: 0,
  },
  suggestion: {
    display: 'block',
  },
  suggestionsList: {
    margin: 0,
    padding: 0,
    listStyleType: 'none',
  },
  divider: {
    height: theme.spacing.unit * 2,
  },
});

const typeProps = {
  author: {
    background: 'url(/img/author-icon.svgz) no-repeat 0 50%',
  },
  book: {
    background: 'url(/img/book-icon.svgz) no-repeat 0 50%',
  }
};

const getSuggestionValue = suggestion => suggestion.value;

function renderSuggestion(suggestion, { query, isHighlighted }) {
  const matches = match(suggestion.value, query);
  const parts = parse(suggestion.value, matches);

  return (
    <MenuItem selected={isHighlighted} component="div">

      <div style={{
          ...typeProps[suggestion.type],
          marginLeft: '-10px',
          paddingLeft:'40px',
          overflow: "hidden",
          textOverflow: "ellipsis",
          textDecoration: "underline",
        }}>
        {parts.map((part, index) =>
          part.highlight ? (
            <span key={String(index)} style={{ fontWeight: 700, color:"#90f8a6" }}>
              {part.text}
            </span>
          ) : (
            <strong key={String(index)} style={{ fontWeight: 300 }}>
              {part.text}
            </strong>
          ),
        )}
      </div>
    </MenuItem>
  );
}

class SearchAutosuggest extends React.Component {

  state = {
    search: '',
    suggestions: [],
  };

  onSuggestionSelected = (event, { suggestion }) => {
    this.props.history.push(suggestion.url);
    this.setState({search: ''});
  }

  handleSubmit = (event) => {
    event.preventDefault();

    if (this.state.search.length > 2){
      this.props.history.push(default_search + this.state.search);
      this.setState({search: ''});

      document.activeElement.blur();
    }
  };

  renderInputComponent = (inputProps) => {
    const { classes, inputRef = () => {}, ref, ...other } = inputProps;
    this.searchInput = React.createRef();

    return (
      <TextField
        fullWidth
        variant={"outlined"}
        InputProps={{
          inputRef: this.searchInput,
          classes: {
            input: classes.input,
          },
        }}
        {...other}
      />
    );
  }

  handleSuggestionsFetchRequested = ({ value }) => {
    const inputValue = deburr(value.trim()).toLowerCase();
    const inputLength = inputValue.length;

    if (inputLength < 2){
      this.setState({
        suggestions: []
      });
      return;
    }

    let suggestions = fetch(api +'?limit=10&query=' +  inputValue)
      .then(response => response.json())
      .then(data => this.setState({ suggestions: data.suggestions }));
  };


  handleSuggestionsClearRequested = () => {
    this.setState({
      suggestions: [],
    });
  };

  handleChange = name => (event, { newValue }) => {
    this.setState({
      [name]: newValue,
    });
  };

  render() {
    const { classes } = this.props;

    const autosuggestProps = {
      getSuggestionValue,
      renderSuggestion,
      focusInputOnSuggestionClick: false,
      onSuggestionSelected: this.onSuggestionSelected.bind(),
      renderInputComponent: this.renderInputComponent.bind(),
      suggestions: this.state.suggestions,
      onSuggestionsFetchRequested: this.handleSuggestionsFetchRequested,
      onSuggestionsClearRequested: this.handleSuggestionsClearRequested,
    };

    return (
      <div className={classes.root}>
        <form className={classes.container} noValidate autoComplete="off" onSubmit={this.handleSubmit}>
          <Autosuggest
            {...autosuggestProps}
            inputProps={{
              classes,
              placeholder: 'Search',
              value: this.state.search,
              onChange: this.handleChange('search'),
            }}
            theme={{
              container: classes.container,
              suggestionsContainerOpen: classes.suggestionsContainerOpen,
              suggestionsList: classes.suggestionsList,
              suggestion: classes.suggestion,
            }}
            renderSuggestionsContainer={options => (
              <Paper {...options.containerProps} square>
                {options.children}
              </Paper>
            )}
          />
        </form>
      </div>
    );
  }
}

SearchAutosuggest.propTypes = {
  history: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
};

export default withStyles(styles)(withRouter(SearchAutosuggest));

