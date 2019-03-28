import React from "react";
import PropTypes from 'prop-types';
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";

import Masonry from "./Masonry";

const api = '/api/v1/news';

export default class Home extends React.Component {

  constructor(props, context) {
    super(props, context);
  }

  state = {
    initialNews: [],
  };


  componentDidMount() {
    if (this.state.initialNews.length == 0){

      fetch(api)
        .then(response => response.json())
        .then(data => this.setState({ initialNews: data.result.items }));
    }
  }

  fetchMoreNews() {
    return [];
  }


  render() {

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>
        <br />
        {
          (this.state.initialNews.length == 0)
            ? <b>All the loading</b>
            : <Masonry
                initialItems={this.state.initialNews}
                fetchAdditionalItems={this.fetchMoreNews.bind()}
            />
        }
      </div>
    );

  }
}

Home.propTypes = {
//  initialProps: PropTypes.object.isRequired,
};

