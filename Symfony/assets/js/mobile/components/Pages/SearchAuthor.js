import React from "react";
import { withRouter } from 'react-router-dom'
import PropTypes from 'prop-types';
import Search from "./Search";
import { withStyles } from '@material-ui/core/styles';


class SearchAuthor extends Search {

  api = '/api/v1/search/author';
  title = 'Books to Love search: ';

  renderDescription = () => {

    const { classes } = this.props;

    return (
      <div className={classes.youSearched}>
        <img className={classes.icon} src="/img/author-icon.svgz" />
        Found&nbsp;
        <strong>{this.state.matches}</strong>&nbsp;
        books by &nbsp;
        <strong>{ this.searchedFor }</strong>
      </div>
    );
  }

}

SearchAuthor.propTypes = {
  query: PropTypes.string.isRequired,
};

export default withStyles(SearchAuthor.styles)(withRouter(SearchAuthor));
