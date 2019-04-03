import React from "react";
import PropTypes from 'prop-types';
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import Loading from "../Trim/Loading";

import Masonry from "../Masonry/Masonry";

const api = '/api/v1/news';

export default class Home extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
      initialNews: [],
      lastFetched: '',
      hasmore: true,
    };
  }


  loading = false;

  componentDidMount() {

    if (this.props.initialProps.items) {
      let items = this.props.initialProps.items;

      this.setState({
        initialNews: items,
        lastFetched: items[items.length -1]._id.$id,
        hasmore: true,
      });
    }
    else{
      fetch(api)
        .then(response => response.json())
        .then(data =>
          this.setState({
            initialNews: data.result.items,
            lastFetched: data.result.items[data.result.items.length -1]._id.$id,
            hasmore: data.result.hasmore,
        }));
    }
  }

  generateRandom = () => {
    return Math.random().toString(36).substring(2, 15) +
      Math.random().toString(36).substring(2, 15);
  }

  fetchMoreNews = (handler) => {

    if (this.state.hasmore && !this.loading) {
      this.loading = true;
      fetch(api + '?idlt=' + this.state.lastFetched)
        .then(response => response.json())
        .then(data => {
          this.loading = false;
          this.setState({
            lastFetched: data.result.items[data.result.items.length -1]._id.$id,
            hasmore: data.result.hasmore,
          });
          handler(data.result.items);
        })
    }
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
            ? <Loading />
            : <Masonry
                initialItems={this.state.initialNews}
                fetchAdditionalItems={this.fetchMoreNews}
            />
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

Home.propTypes = {
};

