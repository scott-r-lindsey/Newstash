import React from "react";
import { withRouter } from 'react-router-dom'
import PropTypes from 'prop-types';
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import { withStyles } from '@material-ui/core/styles';
import * as Constants from '../../constants'

import Masonry from "../Masonry/Masonry";

const api = '/api/v1/search/books';

const styles = theme => ({
  youSearched: {
    backgroundColor: Constants.FireBrick,
    fontFamily: Constants.BoringFont,
    margin: '0px 20px 20px 20px',
    padding: '10px',
    color: 'white',
    boxShadow: '1px 1px 14px rgba(50,50,50,.75)',
    borderRadius: '5px',
  }
});

class SearchTitle extends React.Component {
  constructor(props, context) {
    super(props, context);

    this.page = 1;
    this.search = this.props.location.search;

    this.state = {
      initialWorks: [],
      hasmore: true,
      query: '',
      matches: 0,
    };
  }

  loading = false;

  componentDidMount() {

    console.log('Search title component did mount');

    fetch(api + this.search)
      .then(response => response.json())
      .then(data => {
        this.loading = false;
        this.setState({
          initialWorks: data.result.works,
          hasmore: data.result.hasmore,
          matches: data.result.matches,
          query: data.result.query,
        });
      })

  }

  fetchWorks = (handler) => {
    fetch(api + this.search)
      .then(response => response.json())
      .then(data => {
        this.loading = false;
        this.setState({
          initialWorks: data.result.works,
          hasmore: data.result.hasmore,
          matches: data.result.matches,
          query: data.result.query,
        });
      })

  }

  fetchMoreWorks = (handler) => {

    if (this.state.hasmore && !this.loading) {

      this.loading = true;
      this.page++;

      fetch(api + this.search + '&page=' + this.page)
        .then(response => response.json())
        .then(data => {
          this.loading = false;
          this.setState({
            hasmore: data.result.hasmore,
          });
          handler(data.result.works);
        })
    }
  }

  render() {

    const { classes, query } = this.props;

    return (
      <div>
        <Helmet>
          <title>Search Title</title>
        </Helmet>
        <br />
        {
          (this.state.initialWorks.length == 0)
            ? <b>All the loading</b>
            :
              <div key={this.props.location.key}>
                <div className={classes.youSearched} >
                <strong>{this.state.matches}</strong> books with titles similar to <strong>{ this.state.query }</strong>
                </div>
                <Masonry
                  query={this.state.query}
                  initialItems={this.state.initialWorks}
                  fetchAdditionalItems={this.fetchMoreWorks}
              />
            </div>
        }
        { this.state.hasmore ||
          <div style={{
              textAlign: 'center',
              padding: '60px 0',
              opacity: '.5',
          }}>
            <em> Copyright &copy; { new Date().getFullYear() } Books to Love</em>
          </div>
        }
      </div>
    );
  }
}

SearchTitle.propTypes = {
  query: PropTypes.string.isRequired,
};

export default withStyles(styles)(withRouter(SearchTitle));


