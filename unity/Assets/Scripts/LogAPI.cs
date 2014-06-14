// DEFINE DEBUG HERE IF YOU WANT DEBUG OUTPUT
#define DEBUG
using UnityEngine;
using System;
using System.Threading;
using System.Collections;
using System.Collections.Generic;


namespace Logging
{
	// TODO: Clean up
	// TODO: Replace setup logic with sync methods and callbacks, instead of coroutines that are idling
	public class LogAPI {

		// CONFIG
		public const string Host = "http://localhost/unitylogger/api/log";
		private const int entryQueueSize = 10;

		[HideInInspector]
		public int session_id {get; private set;}
		[HideInInspector]
		public bool Flushing {private set; get;}

		private static LogAPI _instance;

		public static LogAPI instance
		{
			private set { _instance = value; }
			get
			{
				if(_instance == null)
				{
					_instance = new LogAPI();
				}
				return _instance;
			}
		}

		public bool SessionClosed
		{
			get; private set;
		}

		private Queue<LogEntry> entryQueue = new Queue<LogEntry>(entryQueueSize);

		// ----------------------------- Methods -------------------------

		private LogAPI()
		{
			#if DEBUG
				Debug.Log("New LogAPI created");
			#endif
			session_id = 0;
			Flushing = false;
		}

		public IEnumerator RegisterLoggable(Loggable l, Logger logger, Action<int> cb = null)
		{
			while(session_id == 0)
			{
				yield return true;
			}

			while(logger.scene_id == 0)
			{
				yield return true;
			}

			WWWForm form = new WWWForm();
			form.AddField("name", l.name);
			form.AddField("scene_id", logger.scene_id);

			
			WWW www = new WWW(Host + "/register_loggable", form);
			yield return www;

			if(String.IsNullOrEmpty(www.error) == false)
			{
				yield return logger.StartCoroutine(Utils.RetryConnection(www, form));
			}

			JSONObject json = HandleResponse(www);
			#if DEBUG
				Debug.Log("Registered: " + json.ToString());
			#endif
			if(cb != null)
			{
				cb((int) json[0]["id"].n);
			}
		}

		public bool Enqueue(LogEntry e)
		{
			e.session_id = session_id;
			entryQueue.Enqueue(e);
			#if DEBUG
				Debug.Log("Enqueue: " + e.event_name);
			#endif
			if(entryQueue.Count >= entryQueueSize)
			{
				return true;
			}
			return false;
		}

		public int Enqueued()
		{
			return entryQueue.Count;
		}

		public IEnumerator Flush(MonoBehaviour context = null)
		{
			if(Enqueued() <= 0 || Flushing)
				yield return false;

			while(session_id <= 0)
				yield return true;
			
			#if DEBUG
				Debug.Log("Flushing...");
			#endif
			Flushing = true;
			WWWForm form = new WWWForm();
			LogEntry[] entries = new LogEntry[entryQueue.Count];
			entryQueue.CopyTo(entries, 0);
			entryQueue.Clear();
			for(int i = 0; i < entries.Length; i++)
			{
				LogEntry e = entries[i];
				e.ToForm(form, i);
			}
			
			WWW www = new WWW(Host + "/entries", form);
			yield return www;

			if(String.IsNullOrEmpty(www.error) == false && context != null)
			{
				yield return context.StartCoroutine(Utils.RetryConnection(www, form));
			}

			JSONObject json = HandleResponse(www);
			#if DEBUG
				Debug.Log("Entry post: " + json.ToString());
			#endif
			Flushing = false;
		}



		public IEnumerator RegisterScene
		(String name, float time, MonoBehaviour context = null, Action<int> cb = null)
		{
			while(session_id == 0)
			{
				yield return true;
			}

			// if(logger.scene_id != 0)
			// {
			#if DEBUG
				// 	Debug.Log("I want to close the current scene");
			#endif
			// 	yield return logger.StartCoroutine(CloseScene(logger));
			// }

			#if DEBUG
				Debug.Log("Registering scene");
			#endif
			WWWForm form = new WWWForm();
			form.AddField("session_id", session_id);
			form.AddField("name", name);
			form.AddField("time", time.ToString());

			WWW www = new WWW(Host + "/register_scene", form);
			yield return www;
			
			if(String.IsNullOrEmpty(www.error) == false && context != null)
			{
				yield return context.StartCoroutine(Utils.RetryConnection(www, form));
			}
			
			JSONObject json = HandleResponse(www);
			if(cb != null)
			{
				int scene_id = (int) json[0]["id"].n;
				cb(scene_id);
			}
		}

		private JSONObject HandleResponse(WWW www)
		{
			if(String.IsNullOrEmpty(www.error) == false){
				#if DEBUG
					Debug.Log("url: "+ www.url + "\nerror: " + www.error);
				#endif
			}

			JSONObject json = new JSONObject(www.text);
			return json;
		}

		// Still not working
		public IEnumerator CloseScene
		(int scene_id, MonoBehaviour context = null, Action cb = null)
		{
			#if DEBUG
				Debug.Log("Closing scene with id: " + scene_id);
			#endif
			string url = Host + "/close_scene/";
			WWWForm form = new WWWForm();
			form.AddField("id", scene_id);
			WWW www = new WWW(url, form);
			yield return www;

			if(String.IsNullOrEmpty(www.error) == false && context != null)
			{
				yield return context.StartCoroutine(Utils.RetryConnection(www, form));
			}

			HandleResponse(www);
			if(cb != null)
			{
				while(Flushing) yield return true;
				cb();
			}
		}

		/**
		 * Registers a new session server-side
		 */
		public IEnumerator RegisterSession(MonoBehaviour context = null)
		{
			if(session_id != 0)
			{
				yield return false;
			}
			#if DEBUG
				Debug.Log("Registering new session...");
			#endif
			
			WWWForm form = new WWWForm();
			#if DEBUG
				int appv = (Debug.isDebugBuild) ? 1 : 2;
			#endif
			form.AddField("app_version", appv.ToString());	
			form.AddField("MAC", Utils.GetMacAddress());

			WWW www = new WWW(Host + "/register_session", form);
			yield return www;

			if(String.IsNullOrEmpty(www.error) == false && context != null)
			{
				yield return context.StartCoroutine(Utils.RetryConnection(www, form));
			}
			

			JSONObject json = HandleResponse(www);
			session_id = (int) json[0]["id"].n;
			#if DEBUG
				Debug.Log("Session registed with id: " + json[0]["id"]);
			#endif

		}

		public bool IsDry()
		{
			return (session_id == 0);
		}

		public IEnumerator CloseSession(MonoBehaviour context = null){
			WWWForm form = new WWWForm();
			form.AddField("id", session_id);
			WWW www = new WWW(Host + "/close_session", form);
			yield return www;

			if(String.IsNullOrEmpty(www.error) == false && context != null)
			{
				yield return context.StartCoroutine(Utils.RetryConnection(www, form));
			}
			SessionClosed = true;

			#if DEBUG
				Debug.Log("session finished: " + www.text);
			#endif
		}


	}
	
}
