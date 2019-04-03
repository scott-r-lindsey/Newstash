import React from "react";
import { withRouter } from 'react-router-dom'
import PropTypes from 'prop-types';
import Search from "./Search";
import { withStyles } from '@material-ui/core/styles';


class SearchTitle extends Search {

  api = '/api/v1/search/books';
  title = 'Books to Love search: ';

  renderDescription = () => {

    const { classes } = this.props;

    return (
      <div className={classes.youSearched}>
        <img className={classes.icon} src="/img/book-icon.svgz" />
        Found&nbsp;
        <strong>{this.state.matches}</strong>&nbsp;
        books with titles similar to&nbsp;
        <strong>&ldquo;{ this.searchedFor }&rdquo;</strong>
      </div>
    );
  }

}

SearchTitle.propTypes = {
  query: PropTypes.string.isRequired,
};

export default withStyles(SearchTitle.styles)(withRouter(SearchTitle));
